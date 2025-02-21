<?php
  namespace Atua\FilamentFields\Pages\Report;

  use Exception;
  use Filament\Forms\Components\Actions;
  use Filament\Forms\Components\Actions\Action;
  use Filament\Forms\Form;
  use Filament\Notifications\Notification;
  use Filament\Pages\Page;

  abstract class ReportManager extends Page
  {
    /**
     * Stores the target report file name for the POST request.
     * This attribute should be overridden in the child class to define specific rules.
     *
     * @var string
     */
    public string $reportFileName;

    /**
     * Allows child classes to define extra JavaScript code.
     * This attribute should be overridden in the child class to define specific rules.
     *
     * @var string
     */
    public string $childExtraJs = "";

    /**
     * @var string
     */
    public string $reportScripts;

    /**
     * Defines the label used for this page in the navigation menu.
     * This attribute should be overridden in the child class to define specific rules.
     *
     * @var string|null
     */
    public static ?string $navigationLabel = "";

    /**
     * Stores the URL-friendly identifier (slug) for the page.
     * This attribute should be overridden in the child class to define specific rules.
     *
     * @var string|null
     */
    public static ?string $slug = "";

    /**
     * Defines the title of the page.
     * This attribute should be overridden in the child class to define specific rules.
     *
     * @var string|null
     */
    public static ?string $title = "";

    /**
     * Holds the form fields accessed by Livewire,
     * allowing references to the associated components.
     *
     * @var array|null
     */
    public ?array $dynamicProperties;

    /**
     * Sets the standard view file for the reports.
     *
     * @var string
     */
    protected static string $view = "filament-fields::reports";

    /**
     * Holds the name of the function for which users must have
     * the necessary permissions to access certain system features.
     * This attribute should be overridden in the child class to define specific rules.
     *
     * @var string
     */
    protected static string $functionName = "";

    /**
     * Store the form submited data
     * @var array
     */
    protected array $formData;

    const ID_NOTIFICATION_DANGER  = 0;
    const ID_NOTIFICATION_SUCCESS = 1;
    const ID_NOTIFICATION_WARNING = 2;

    /**
     * Executes validations on the filtered field.
     *
     * @param array $formData
     * @return void
     * @throws Exception
     */
    abstract protected function validateFilteredFields(array $formData): void;

    /**
     * Retrieves and returns the hidden fields used for form control.
     */
    abstract protected function getHiddenFields(): array;

    /**
     * Retrieves and returns the filter fields for the report.
     */
    abstract protected function getFiltersFields(): array;

    /**
     * Retrieves and returns the configuration fields for the report.
     */
    abstract protected function getConfigFields(): array;

    /**
     * Returns the report form
     * @param Form $form
     * @return Form
     * @throws Exception
     */
    public function form(Form $form): Form
    {
      $this->setScripts();

      return $form
        ->schema([
          ...$this->getHiddenFields(),
          ...$this->getFiltersFields(),
          ...$this->getConfigFields(),
          ...$this->getActionsButtons(),
        ])->statePath("dynamicProperties");
    }

    /**
     * Injects custom JavaScript code specific to the child class.
     * @return void
     */
    protected function getCustomChildJs(): void {}

    /**
     * Retrieves and returns the action buttons for the form.
     * @return array
     */
    protected function getActionsButtons(): array
    {
      return [
        Actions::make([
          Action::make("submit")
            ->label("Gerar Relatório")
            ->submit("submit")
            ->button(),

          Action::make("reset")
            ->label("Limpar Filtros")
            ->action(function($livewire){
              $livewire->form->fill();
            })
            ->resetFormData()
        ])
          ->columnSpanFull()
      ];
    }

    /**
     * Initializes the component properties before rendering.
     * This method is executed once when the component is first loaded.
     * Equivalent to the constructor in Livewire components.
     *
     * @return void
     */
    public function mount(): void
    {
      $this->form->fill();
    }

    /**
     * @return void
     */
    protected function setScripts(): void
    {
      $this->reportScripts = <<<JS
        function retorna_rel(arquivo)
        {
          window.open(arquivo, '', 'width='+window.screen.width.toString()+', height='+window.screen.height.toString());
        }

        function openReport()
        {
          pop_open('', 500, 450, 'report', 'yes');
          return true;
        }

        function pop_open(address, width, height, windowName, resizable)
        {
          var topo = 0;
          var esq = (screen.availWidth - width);
          var __BASE_URL__ = '';

          if (windowName == false)
            windowName = '';
          if (!resizable)
            resizable = 'yes';

          if (window.BASE_URL && window.BASE_URL != '' && address != '')
          {
            if ("http" != address.substr(0, 4).toLowerCase() && "ftp" != address.substr(0, 3).toLowerCase())
              __BASE_URL__ = window.BASE_URL;

            address = __BASE_URL__ + address.replace(/\.\.\//g, '');

            //TODO: MANDAR PARA AZURE
          }

          var pop_window = window.open(address, windowName,'width='+width+',height='+height+',top='+topo+',left='+esq+',location=no,status=no,menubar=no,resizable=' + resizable + ',scrollbars=yes');

          pop_window.focus();

          return pop_window;
        }

        /**
        * Builds a dynamic form fields during runtime
        * @param form
        * @param url
        * @param arrFields
        */
        function buildFormReport(form, url, arrFields)
        {
          form.method    = 'POST';
          form.action    = url;
          form.target    = 'report';

          for (const [key, value] of Object.entries(arrFields))
          {
            if (value === '' || value === null)
              continue;

            var input = document.createElement('input');

            input.type  = 'hidden';
            input.name  = key;
            input.value = value;

            form.appendChild(input);
          }
        }

        document.addEventListener('DOMContentLoaded', function () {
          Livewire.on('open-report', function (dados) {
            try
            {
              var arrFields = dados[0].fields;
              var url       = dados[0].url;

              openReport();

              var form = document.createElement('form');

              buildFormReport(form, url, arrFields);
              document.body.appendChild(form);
              form.submit();
            }
            catch (error)
            {
              console.error("Error while generating report:", error);
            }
          });
        });
JS;

      if (!isset($this->childExtraJs) && method_exists(get_called_class(), 'getCustomChildJs'))
      {
        $this->getCustomChildJs();

        if (isset($this->childExtraJs))
          $this->reportScripts .= "\n" . $this->childExtraJs;
      }
    }

    /**
     * @return void
     */
    public function submit(): void
    {
      try
      {
        $this->getFormSubmitedData();
        $this->validateFilteredFields($this->formData["fields"]);
        $this->setTargetReport();

        if (empty($this->formData["fields"]))
          throw new Exception("Nenhum filtro informado para gerar o relatório.", self::ID_NOTIFICATION_WARNING);

        $this->dispatch("open-report", $this->formData);
        $this->sendCustomNotification("Gerando relatorio...", self::ID_NOTIFICATION_SUCCESS);
      }
      catch (Exception $e)
      {
        $this->sendCustomNotification($e->getMessage(), $e->getCode());
      }
    }

    /**
     * Gets the form submited data.
     *
     * @return void
     */
    protected function getFormSubmitedData(): void
    {
      $this->formData["fields"] = $this->form->getState();

      foreach ($this->formData["fields"] as $key => $value)
        if ($this->formData["fields"][$key] == "" && $this->formData["fields"][$key] == null)
          unset($this->formData["fields"][$key]);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function setTargetReport(): void
    {
      $this->validateReportDestinationDefined();
      $this->defineURLPostFields();
    }

    /**
     * Validates whether the report destination file is defined in the child class.
     * Throws an error if the file is not specified
     *
     * @return void
     * @throws Exception
     */
    protected function validateReportDestinationDefined(): void
    {
      if (!isset($this->reportFileName))
        throw new Exception("Arquivo do Relatório não definido na classe filha.", self::ID_NOTIFICATION_WARNING);

      $this->validateReportDestinationExists();
    }

    /**
     * Validates if the report destination file exists. Throws an error if the file is not found
     * @return void
     * @throws Exception
     */
    protected function validateReportDestinationExists(): void
    {
      $dsUrlDestino = EFESUS . "adm/" . $this->reportFileName;

      if (!file_exists($dsUrlDestino))
        throw new Exception("O arquivo de relatório não foi encontrado no destino: " . $dsUrlDestino, self::ID_NOTIFICATION_DANGER);
    }

    /**
     * @return void
     */
    protected function defineURLPostFields(): void
    {
      $this->formData["url"] = $_ENV["APP_URL"] . "adm/" . $this->reportFileName;
    }

    /**
     * Validates the date range, ensuring that the start date is earlier than the end date
     * @param string      $fieldName
     * @param string|null $startDate
     * @param string|null $endDate
     * @return void
     * @throws Exception
     */
    protected static function validateFieldRangeDate(string $fieldName, ?string $startDate = null, ?string $endDate = null): void
    {
      if (!str_value($startDate) || !str_value($endDate))
        return;

      if ($startDate > $endDate)
        throw new Exception("O campo <b>{$fieldName} &gt;=</b> deve ser menor ou igual ao campo <b>{$fieldName} &lt;=</b>!", self::ID_NOTIFICATION_WARNING);
    }

    /**
     * Sends a notification with a custom message.
     *
     * @param string $message
     * @param int    $idTipoNotificacao
     * @return void
     */
    protected function sendCustomNotification(string $message, int $idTipoNotificacao = self::ID_NOTIFICATION_WARNING): void
    {
      switch ($idTipoNotificacao)
      {
        case self::ID_NOTIFICATION_SUCCESS:
          Notification::make()
            ->title("Sucesso")
            ->success()
            ->body($message)
            ->seconds(5)
            ->send();
        break;
        case self::ID_NOTIFICATION_WARNING:
          Notification::make()
            ->title("Atenção")
            ->warning()
            ->body($message)
            ->seconds(5)
            ->send();
        break;
        default:
          Notification::make()
            ->title("Erro")
            ->danger()
            ->body($message)
            ->seconds(5)
            ->send();
        break;
      }
    }

    /**
     * Validates access permissions for the report.
     * It checks whether a function is defined in the class attribute;
     * if not, it validates based on the report folder name.
     *
     * @return bool
     */
    public static function canAccess(): bool
    {
      $className = get_called_class();

      if (property_exists($className, "functionName") && filled($className::$functionName))
        $functionName = $className::$functionName;
      else
      {
        $functionName = explode("\\", $className);
        $functionName = $functionName[count($functionName) - 2];
        $functionName = strtolower(preg_replace("/(?<!^)[A-Z]/", "_$0", $functionName));
      }

      return (bool) valida_permissao_funcao($functionName);
    }
  }

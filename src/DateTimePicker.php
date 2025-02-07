<?php

namespace Atua\FilamentFields;

use Filament\Forms\Components\TextInput;
use Atua\FilamentFields\Enums\DateTimeFormat;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Set;
use \Exception;
use \DateTime;
use \DateTimeZone;

class DateTimePicker extends TextInput
{
  protected DateTimeFormat $format = DateTimeFormat::DDMMYYYYHHMM;

  /**
   * @return void
   */
  protected function setUp(): void
  {
    $this
      ->extraAlpineAttributes(fn () => $this->getAlpineAttributes())
      ->formatStateUsing(fn (?string $state) => $this->hydrateDate($state))
      ->mask($this->format->value)
      ->dehydrateStateUsing(fn (?string $state) => $this->dehydrateDate($state))
      ->initDatePickerAction()
      ->rule(["date" => "date_format:{$this->getDateTimeMaskPHP()}"]);
  }

  /**
   * @param bool $useActualDateTime
   * @return $this
   * @throws Exception
   */
  public function useActualDateTime(bool $useActualDateTime = true): static
  {
    if ($useActualDateTime)
    {
      $DateTime = new DateTime("now", new DateTimeZone(env("APP_TIMEZONE")));

      $this->suffixAction(
        Action::make("setDefaultDate")
          ->label("Selecionar Data/Hora Atual")
          ->extraAttributes(["tabindex" => "-1"])
          ->icon("heroicon-o-clock")
          ->disabled(fn () => $this->isDisabled() || $this->isReadonly())
          ->action(function (Set $set) use ($DateTime) {
            $set($this->getName(), $DateTime->format($this->getDateTimeMaskPHP()));
          })
      );
    }

    return $this;
  }

  /**
   * @param DateTimeFormat $format
   * @return $this
   */
  public function format(DateTimeFormat $format): static
  {
    $this->format = $format;

    $this
      ->mask($format->value)
      ->rule(["date" => "date_format:{$this->getDateTimeMaskPHP()}"]);

    return $this;
  }

  /**
   * @return $this
   */
  protected function initDatePickerAction(): static
  {
    $this->suffixAction(
      Action::make("openDatePicker")
        ->label("Abrir calendário")
        ->extraAttributes(["tabindex" => "-1"])
        ->icon("heroicon-o-calendar")
        ->disabled(fn () => $this->isDisabled() || $this->isReadonly())
        ->action(function ($livewire) {
          $livewire->js('
            setTimeout(() => {
              let inputId = "' . $this->getId() . '";
              let escapedId = CSS.escape(inputId);
              let input = document.querySelector("#" + escapedId);
              let append = input.closest(`[x-ref="modalContainer"]`);

              if (append === null)
                append = document.body;

              let fp = flatpickr(input, {
                enableTime: ' . ($this->showTimeInDatePicker() ? "true" : "false") . ',
                enableSeconds: ' . ($this->showSecondsInDatePicker() ? "true" : "false") . ',
                dateFormat: "' . $this->getDateTimeMaskDatePicker() .  '" ,
                allowInput: true,
                defaultHour: "' . date("H") . '",
                defaultMinute: "' . date("i") . '",
                time_24hr: true,
                locale: "pt",
                disableMobile: "true",
                minuteIncrement: 1,
                clickOpens: false,
                appendTo: append,
                closeOnSelect: true,
                onClose: function () {
                  let modelAttribute = null;

                  if (input.getAttribute("wire:model"))
                    modelAttribute = "wire:model";

                  if (input.getAttribute("wire:model.live"))
                    modelAttribute = "wire:model.live";

                  if (modelAttribute === null)
                    return;

                  $wire.set(input.getAttribute(modelAttribute), input.value);

                  if (fp)
                    fp.destroy();
                },
                onOpen: function () {
                  const calendar = this.calendarContainer;

                  function getAbsolutePosition(element) {
                    let top = 0, left = 0;

                    while (element) {
                      top += element.offsetTop - element.scrollTop + element.clientTop;
                      left += element.offsetLeft - element.scrollLeft + element.clientLeft;
                      element = element.offsetParent;
                    }

                    return { top, left };
                  }

                  setTimeout(() => {
                    const position = getAbsolutePosition(input);
                    const height = input.offsetHeight;

                    if (calendar) {
                      calendar.style.position = `absolute`;
                      calendar.style.top = `${position.top + height}px`;
                      calendar.style.left = `${position.left}px`;
                    }
                  }, 10);
                }
              });

              fp.open();
            }, 15);
          ');
        })
    );

    return $this;
  }

  /**
   * @param string|null $state
   * @return string|null
   */
  protected function hydrateDate(?string $state = null): ?string
  {
    if (blank($state))
      return null;

    try
    {
      $DateTime = new DateTime($state, new DateTimeZone(env("APP_TIMEZONE")));
      return $DateTime->format($this->getDateTimeMaskPHP());
    }
    catch (Exception)
    {
      return null;
    }
  }

  /**
   * @param string|null $state
   * @return string|null
   */
  protected function dehydrateDate(?string $state = null): ?string
  {
    if (blank($state))
      return null;

    try
    {
      $DateTime = DateTime::createFromFormat($this->getDateTimeMaskPHP(), $state, new DateTimeZone(env("APP_TIMEZONE")));
      return $DateTime->format($this->getDehydrateMask());
    }
    catch (Exception)
    {
      return null;
    }
  }

  /**
   * @return string
   */
  protected function getDehydrateMask(): string
  {
    return match ($this->format)
    {
      DateTimeFormat::DDMMYYYYHHMMSS, DateTimeFormat::DDMMYYYYHHMM => "Y-m-d H:i:s",
      default => "Y-m-d",
    };
  }

  /**
   * @return bool
   */
  protected function showTimeInDatePicker(): bool
  {
    return in_array($this->format, [DateTimeFormat::DDMMYYYYHHMMSS, DateTimeFormat::DDMMYYYYHHMM]);
  }

  protected function showSecondsInDatePicker(): bool
  {
    return $this->format === DateTimeFormat::DDMMYYYYHHMMSS;
  }

  /**
   * @return string
   */
  protected function getDateTimeMaskPHP(): string
  {
    return match ($this->format)
    {
      DateTimeFormat::DDMMYY => "d/m/y",
      DateTimeFormat::DDMMYYYY => "d/m/Y",
      DateTimeFormat::DDMMYYYYHHMM => "d/m/Y H:i",
      DateTimeFormat::DDMMYYYYHHMMSS => "d/m/Y H:i:s",
    };
  }

  /**
   * @return string
   */
  protected function getDateTimeMaskDatePicker(): string
  {
    return match ($this->format)
    {
      DateTimeFormat::DDMMYY => "d/m/y",
      DateTimeFormat::DDMMYYYY => "d/m/Y",
      DateTimeFormat::DDMMYYYYHHMM => "d/m/Y H:i",
      DateTimeFormat::DDMMYYYYHHMMSS => "d/m/Y H:i:S",
    };
  }

  /**
   * @return string
   */
  protected function getDateTimeMaskJS(): string
  {
    return match ($this->format)
    {
      DateTimeFormat::DDMMYY => "DD/MM/YY",
      DateTimeFormat::DDMMYYYY => "DD/MM/YYYY",
      DateTimeFormat::DDMMYYYYHHMM => "DD/MM/YYYY HH:mm",
      DateTimeFormat::DDMMYYYYHHMMSS => "DD/MM/YYYY HH:mm:ss",
    };
  }

  /**
   * @return array
   */
  protected function getAlpineAttributes(): array
  {
    return array_merge(
      ["autocomplete" => "off"],
      $this->getOnBlur(),
    );
  }

  /**
   * @return string[]
   */
  protected function getXInit(): array
  {
    return [
      'x-data' => '{}',
      'x-init' => '
        let append = $el.closest(`[x-ref="modalContainer"]`);

        if (append === null)
          append = document.body;

        const initDatePicker = (append) => {
          flatpickr($el, {
            enableTime: ' . ($this->showTimeInDatePicker() ? "true" : "false") . ',
            dateFormat: "' . $this->getDateTimeMaskDatePicker() .  '" ,
            allowInput: true,
            time_24hr: true,
            closeOnSelect: true,
            appendTo: append,
            onOpen: function () {
              const calendar = this.calendarContainer;
              const input = $el;

              function getAbsolutePosition(element) {
                let top = 0, left = 0;

                while (element) {
                  top += element.offsetTop - element.scrollTop + element.clientTop;
                  left += element.offsetLeft - element.scrollLeft + element.clientLeft;
                  element = element.offsetParent;
                }
                return { top, left };
              }

              setTimeout(() => {
                const position = getAbsolutePosition(input);
                const height = input.offsetHeight;

                if (calendar) {
                  calendar.style.position = `absolute`;
                  calendar.style.top = `${position.top + height}px`;
                  calendar.style.left = `${position.left}px`;
                }
              }, 10);
            }
          })
        };

        initDatePicker(append);
      ',
    ];
  }

  /**
   * @return string[]
   */
  protected function getOnBlur(): array
  {
    $maskJS = $this->getDateTimeMaskJS();

    return [
      'x-on:blur' => 'function() {
        let modelAttribute = null;

        if ($el.getAttribute("wire:model"))
          modelAttribute = "wire:model";

        if ($el.getAttribute("wire:model.live"))
          modelAttribute = "wire:model.live";

        if (modelAttribute === null)
          return;

        if ($el.getAttribute(modelAttribute))
        {
          const pError = $el.closest("#" + $el.getAttribute(modelAttribute).replaceAll(".", "-") + "-data-invalida");

          if (pError)
            pError.remove();
        }

        if (!$el.value)
          return;

        if ($el.value.length !== "' . $maskJS . '".length)
        {
          const input = $el.value.replace(/[^0-9/: ]/g, "");
          const currentDate = moment();

          const [day, month, yearTime] = input.split("/");
          const [year, time] = (yearTime || "").split(" ");
          const [hour, minute] = (time || "").split(":");

          const formattedDate = moment({
            year: year ? parseInt(year, 10) : currentDate.year(),
            month: month ? parseInt(month, 10) - 1 : currentDate.month(),
            day: day ? parseInt(day, 10) : currentDate.date(),
            hour: hour ? parseInt(hour, 10) : currentDate.hour(),
            minute: minute ? parseInt(minute, 10) : currentDate.minute(),
            second: currentDate.second(),
          });

          if (formattedDate.isValid())
          {
            $el.value = formattedDate.format("' . $maskJS . '");
            $wire.set($el.getAttribute(modelAttribute), $el.value);
          }
        }

        if (
          $el.value.length !== "' . $maskJS . '".length ||
          !moment($el.value, "' . $maskJS . '").isValid()
        )
        {
          const errorElement = document.createElement("p");
          errorElement.classList.add("text-danger", "text-sm");
          errorElement.setAttribute("id", $el.getAttribute(modelAttribute).replaceAll(".", "-") + "-data-invalida");
          errorElement.style.color = "red";
          errorElement.textContent = "Data inválida!";

          if ($el.parentNode.parentNode.parentNode)
            $el.parentNode.parentNode.parentNode.appendChild(errorElement);

          $el.value = "";
          $wire.set($el.getAttribute(modelAttribute), $el.value);
        }
      }',
    ];
  }
}

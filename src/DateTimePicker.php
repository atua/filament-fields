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
      ->extraAlpineAttributes(fn () => $this->getOnBlur())
      ->formatStateUsing(fn (?string $state) => $this->hydrateDate($state))
      ->mask($this->format->value)
      ->dehydrateStateUsing(fn (?string $state) => $this->dehydrateDate($state))
      ->rule(["date" => "date_format:{$this->getDateTimeMaskPHP()}"]);
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
          ->icon("heroicon-o-clock")
          ->action(function (Set $set) use ($DateTime) {
            $set($this->getName(), $DateTime->format($this->getDateTimeMaskPHP()));
          })
      );
    }

    return $this;
  }

  /**
   * @param ?string $state
   * @return string
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
   * @param ?string $state
   * @return string
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

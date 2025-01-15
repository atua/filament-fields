<?php

namespace Atua\FilamentFields;

use ArchTech\Money\Currency;
use Closure;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;
use Atua\FilamentFields\Currencies\BRL;

class Money extends TextInput
{
  protected ?Currency $currency = null;
  protected int $precision = 2;

  protected function setUp(): void
  {
    $this
      ->currency()
      ->maxLength(17)
      ->extraAlpineAttributes(fn() => $this->getOnInputOrPaste())
      ->formatStateUsing(fn($state) => $this->hydrateCurrency($state))
      ->dehydrateStateUsing(fn($state) => $this->dehydrateCurrency($state));
  }

  public function currency(string|null|Closure $currency = BRL::class): static
  {
    $this->currency = new ($currency);
    currencies()->add($currency);

    if ($currency !== 'BRL')
    {
      $this->prefix(null);
    }

    return $this;
  }

  public function precision(int $precision = 2): static
  {
    $this->precision = $precision;

    return $this;
  }

  protected function hydrateCurrency($state): string
  {
    return $this->formatNumber($state, $this->precision);
  }

  public static function formatNumber($value, $precision = 2, $toFormat = 'pt_BR'): string
  {
    if ($toFormat === 'pt_BR')
      return number_format($value, $precision, ',', '.');

    if ($toFormat === 'sys')
    {
      $value = str_replace('.', '', $value);
      $value = str_replace(',', '.', $value);

      return $value;
    }

    return $value; // Retorna o valor original se o formato não for reconhecido
  }

  protected function dehydrateCurrency($state): int|float|string|null
  {
    if (empty($state))
      return null;

    return $this->formatNumber($state, $this->precision, 'sys');
  }

  protected function getOnInputOrPaste(): array
  {
    $currency        = new ($this->getCurrency());
    $numberFormatter = $currency->locale;
    $precision       = $this->precision; // Usa a propriedade dinâmica para definir as casas decimais

    return [
      'x-on:input' => 'function() {
            $el.value = Currency.masking($el.value, {locales:\'' . $numberFormatter . '\', digits: ' . $precision . ', empty: true, viaInput: true});
            $wire.set($el.getAttribute(\'wire:model\'), $el.value);
           }',
    ];
  }

  public function getCurrency(): ?Currency
  {
    return $this->currency;
  }
}

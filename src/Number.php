<?php

namespace Atua\FilamentFields;

use Filament\Forms\Components\TextInput;

class Number extends TextInput
{
  protected int $precision = 2;

  protected function setUp(): void
  {
    $this
      ->maxLength(17)
      ->extraInputAttributes(['class' => 'text-right'])
      ->extraAlpineAttributes(fn() => $this->getOnInputOrPaste())
      ->formatStateUsing(fn($state) => $this->hydrateFormat($state))
      ->dehydrateStateUsing(fn($state) => $this->dehydrateFormat($state));
  }

  public function precision(int $precision = 2): static
  {
    $this->precision = $precision;

    return $this;
  }

  protected function hydrateFormat($state): string
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

    return $value;
  }

  protected function dehydrateFormat($state): int|float|string|null
  {
    if (empty($state))
      return null;

    return $this->formatNumber($state, $this->precision, 'sys');
  }

  protected function getOnInputOrPaste(): array
  {
    $numberFormatter = 'pt-BR';
    $precision       = $this->precision;

    return [
      'x-on:input' => 'function() {
            $el.value = Currency.masking($el.value, {locales:\'' . $numberFormatter . '\', digits: ' . $precision . ', empty: true, viaInput: true});
            $wire.set($el.getAttribute(\'wire:model\'), $el.value);
           }',
    ];
  }
}

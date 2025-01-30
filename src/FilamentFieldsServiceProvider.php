<?php

namespace Atua\FilamentFields;

use Filament\FilamentServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Validator;
use Spatie\LaravelPackageTools\Package;

class FilamentFieldsServiceProvider extends FilamentServiceProvider
{
  public function packageBooted(): void
  {
    parent::packageBooted();

    Validator::extend('gtNumber', function ($attribute, $value, $parameters, $validator) {

      [$keyMinField, $valueMinField] = explode(".", $parameters[0]);

      $data = $validator->getData();

      $minValue = preg_replace('/\D/', '', $data[$keyMinField][$valueMinField] ?? 0);
      $value    = preg_replace('/\D/', '', $value);

      return $value > $minValue;
    });


    Validator::replacer('gtNumber', function ($message, $attribute, $rule, $parameters, $validator) {
      $attribute     = $validator->customAttributes[$attribute] ?? $attribute;
      $parameters[0] = $validator->customAttributes[$parameters[0]] ?? $parameters[0];

      return "O campo $attribute deve ser maior que o campo {$parameters[0]}.";
    });

    Validator::extend('gteNumber', function ($attribute, $value, $parameters, $validator) {
      [$keyMinField, $valueMinField] = explode(".", $parameters[0]);

      $data = $validator->getData();

      $minValue = preg_replace('/\D/', '', $data[$keyMinField][$valueMinField] ?? 0);
      $value    = preg_replace('/\D/', '', $value);

      return $value >= $minValue;
    });

    Validator::replacer('gteNumber', function ($message, $attribute, $rule, $parameters, $validator) {
      $attribute     = $validator->customAttributes[$attribute] ?? $attribute;
      $parameters[0] = $validator->customAttributes[$parameters[0]] ?? $parameters[0];

      return "O campo $attribute deve ser maior ou igual ao campo {$parameters[0]}.";
    });

    Validator::extend('ltNumber', function ($attribute, $value, $parameters, $validator) {
      [$keyMinField, $valueMinField] = explode(".", $parameters[0]);

      $data = $validator->getData();

      $minValue = preg_replace('/\D/', '', $data[$keyMinField][$valueMinField] ?? 0);
      $value    = preg_replace('/\D/', '', $value);

      return $value < $minValue;
    });

    Validator::replacer('ltNumber', function ($message, $attribute, $rule, $parameters, $validator) {
      $attribute     = $validator->customAttributes[$attribute] ?? $attribute;
      $parameters[0] = $validator->customAttributes[$parameters[0]] ?? $parameters[0];

      return "O campo $attribute deve ser menor que o campo {$parameters[0]}.";
    });

    Validator::extend('lteNumber', function ($attribute, $value, $parameters, $validator) {
      [$keyMinField, $valueMinField] = explode(".", $parameters[0]);

      $data = $validator->getData();

      $minValue = preg_replace('/\D/', '', $data[$keyMinField][$valueMinField] ?? 0);
      $value    = preg_replace('/\D/', '', $value);

      return $value <= $minValue;
    });

    Validator::replacer('lteNumber', function ($message, $attribute, $rule, $parameters, $validator) {
      $attribute     = $validator->customAttributes[$attribute] ?? $attribute;
      $parameters[0] = $validator->customAttributes[$parameters[0]] ?? $parameters[0];

      return "O campo $attribute deve ser menor ou igual ao campo {$parameters[0]}.";
    });


    FilamentAsset::register([
      Js::make('money-script', __DIR__ . '/../resources/js/money.js'),
      Js::make('moment-script', __DIR__.'/../resources/js/moment.min.js'),
      Js::make('flatpickr-script', __DIR__.'/../resources/js/flatpickr.min.js'),
      Js::make('flatpickr-pt', __DIR__.'/../resources/js/flatpickr-pt.js'),
      Css::make('flatpickr-css', __DIR__.'/../resources/css/flatpickr.min.css'),
    ]);
  }

  public function configurePackage(Package $package): void
  {
    /*
     * This class is a Package Service Provider
     *
     * More info: https://github.com/spatie/laravel-package-tools
     */
    $package
      ->name('filament-fields')
      ->hasConfigFile()
      ->hasViews();
  }
}

# Atua Filament Fields

This package is a fork of the [filament-ptbr-form-fields](https://github.com/leandrocfe/filament-ptbr-form-fields) project by [Leandrocfe](https://github.com/leandrocfe), where we made adjustments and created new field formats as well.

## Installation

You can install the package via Composer:

```bash
composer require atua/filament-fields:dev-master
```

## Usage

### DateTimePicker
```php
use Atua\FilamentFields\DateTimePicker;

DateTimePicker::make('dt_inicio')
```

We can use this field to create a date or date + time field, to do this just use the format function:

```php
use Atua\FilamentFields\DateTimePicker;
use Atua\FilamentFields\Enums\DateTimeFormat;

DateTimePicker::make('dt_inicio')
    ->format(DateTimeFormat::DDMMYY)

# the allowed formats are specified in the Atua\FilamentFields\Enums\DateTimeForma enum
```

You can also add the option to select the current date/time using the useActualDateTime function:

```php
use Atua\FilamentFields\DateTimePicker;
use Atua\FilamentFields\Enums\DateTimeFormat;

DateTimePicker::make('dt_inicio')
    ->useActualDateTime()
```

### CPF / CNPJ

To create a dynamic input that accepts either CPF or CNPJ, use:

```php
use Atua\FilamentFields\Document;
//CPF or CNPJ
Document::make('cpf_or_cnpj')
    ->dynamic()
```

If you want to create an input that only accepts CPF or only accepts CNPJ, use:

```php
//CPF
Document::make('cpf')
    ->cpf()
```

```php
//CNPJ
Document::make('cnpj')
    ->cnpj()
```

If you want to use a custom mask for the input, use the cpf() or cnpj() method with a string argument representing the desired mask:

```php
Document::make('cpf')
    ->cpf('999999999-99')
```

```php
Document::make('cnpj')
    ->cnpj('99999999/9999-99')
```

### Validation
`Document` uses [LaravelLegends/pt-br-validator](https://github.com/LaravelLegends/pt-br-validator) to validate Brazilian Portuguese fields by default - `cpf_ou_cnpj` | `cpf` | `cnpj`

You can disable validation using the `validation(false)` method:

```php
Document::make('cpf_or_cnpj')
    ->validation(false)
    ->dynamic()
```

```php
Document::make('cpf')
    ->validation(false)
    ->cpf()
```

### Phone number

To create a dynamic input that formats phone numbers with DDD, use:

```php
use Atua\FilamentFields\PhoneNumber;
PhoneNumber::make('phone_number')
```

If you want to use a custom phone number format, use the `mask() method with a string argument representing the desired format:

```php
PhoneNumber::make('phone_number')
    ->mask('(99) 99999-9999')
```

```php
PhoneNumber::make('phone_number')
    ->mask('+99 (99) 99999-9999')
```

### Money

To create a money input field, use the following syntax:

```php
use Atua\FilamentFields\Money;
Money::make('price')
    ->default('100,00')

#output: 100.00
```

This is suitable for use with `decimal` or `float` data types.

#### Using Integer Values

If you prefer to work with integer values, you can format the money input using the `intFormat()` method:

```php
use Atua\FilamentFields\Money;
Money::make('price')
    ->default(10000)
    ->intFormat()

#output: 10000
```
#### Getting the Raw State

To retrieve the raw state of the field, you can use the `dehydratedMask() method:

```php
use Atua\FilamentFields\Money;
Money::make('price')
    ->default('100,00')
    ->dehydrateMask()

#output: 100,00
```

If you need to remove the prefix from the money input, simply pass null to the `prefix()` method:
```php
Money::make('price')
    ->prefix(null)
```
#### Currency Formatting

This package leverages the `archtechx/money` package under the hood. By default, it uses the `BRL` (Brazilian Real) format for currency values.

If you want to switch to the `USD` (United States Dollar) format, you can do so with the following code:
```php
use Atua\FilamentFields\Currencies\USD;

Money::make('price')
    ->currency(USD::class)
    ->prefix('$')
```

You can also define custom currencies to suit your specific needs:


```php

/*
 * app/Currencies/EUR.php
 */
 
declare(strict_types=1);

namespace App\Currencies;

use ArchTech\Money\Currency;

class EUR extends Currency
{
    /*
     * Code of the currency.
     */
    public string $code = 'EUR';

    /*
     * Name of the currency.
     */
    public string $name = 'Euro';

    /*
     * Rate of this currency relative to the default currency.
     */
    public float $rate = 1.0;

    /*
     * Number of decimals used in money calculations.
     */
    public int $mathDecimals = 2;

    /*
     * Number of decimals used in the formatted value
     */
    public int $displayDecimals = 2;

    /*
     * How many decimals of the currency's values should get rounded
     */
    public int $rounding = 2;

    /*
     * Prefix placed at the beginning of the formatted value.
    */
    public string $prefix = '€';

    /*
     * The language code.
     */
    public string $locale = 'pt';

    /*
     * The character used to separate the decimal values.
     */
    public string $decimalSeparator = '.';

    /*
     * The character used to separate groups of thousands
     */
    public string $thousandsSeparator = ',';
}

```

```php
use App\Currencies\EUR;

Money::make('price')
->currency(EUR::class)
->prefix('€')
```

### Address

To integrate with the ViaCep API for CEP validation and address autofill, use:

```php
use Atua\FilamentFields\Cep;
use Filament\Forms\Components\TextInput;
Cep::make('postal_code')
    ->viaCep(
        mode: 'suffix', // Determines whether the action should be appended to (suffix) or prepended to (prefix) the cep field, or not included at all (none).
        errorMessage: 'CEP inválido.', // Error message to display if the CEP is invalid.

        /**
         * Other form fields that can be filled by ViaCep.
         * The key is the name of the Filament input, and the value is the ViaCep attribute that corresponds to it.
         * More information: https://viacep.com.br/
         */
        setFields: [
            'street' => 'logradouro',
            'number' => 'numero',
            'complement' => 'complemento',
            'district' => 'bairro',
            'city' => 'localidade',
            'state' => 'uf'
        ]
    ),

TextInput::make('street'),
TextInput::make('number'),
TextInput::make('complement'),
TextInput::make('district'),
TextInput::make('city'),
TextInput::make('state'),
```

The mode parameter specifies whether the search action should be appended to or prepended to the CEP field, using the values suffix or prefix. Alternatively, you can use the none value with the `->live(onBlur: true)` method to indicate that the other address fields will be automatically filled only when the CEP field loses focus.

### Relatorio

To create a form filter for reports, you can use a custom Artisan command:

```
php artisan make:report ReportName
```

The script will prompt you to enter the `name` of the new filter and will generate a new class in `app\Filament\Pages\Reports\ReportName`. 
Simply follow the script's instructions, and the filter will be easily accessible without any additional configuration. You will only need to focus on implementing the form fields and any extra logic specific to your report.

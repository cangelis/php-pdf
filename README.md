# HTML to PDF Converter based on wkhtmltopdf for PHP

This is a yet another html to pdf converter for php. This package uses [wkhtmltopdf](https://github.com/antialize/wkhtmltopdf) as a third-party tool so `proc_*()` functions have to be enabled in your php configurations and `wkhtmltopdf` tool should be installed in your machine (You can download it from [here](https://code.google.com/p/wkhtmltopdf/downloads/list)).

Check out the [Laravel version](https://github.com/cangelis/l4pdf) if you're using Laravel Framework.

## Installation

Add this to your `composer.json`

    {
        "require": {
            "cangelis/pdf": "1.1.*"
        }
    }

and run `composer.phar update`

## Some examples

    $pdf = new CanGelis\PDF\PDF('/usr/bin/wkhtmltopdf');

    echo $pdf->loadHTML('<b>Hello World</b>')->generatePDF();

    echo $pdf->loadURL('http://www.cangelis.com')->save('/home/can/cangelis.pdf');

    echo $pdf->loadURL('http://www.laravel.com')->grayscale()->pageSize('A3')->orientation('Landscape')->generatePDF();

    echo $pdf->loadHTMLFile('/home/can/index.html')->lowquality()->pageSize('A2')->generatePDF();

## Documentation

You can see all the available methods in the full [documentation](https://github.com/cangelis/php-pdf/blob/master/DOCUMENTATION.md) file

## Contribution

Feel free to contribute!

# HTML to PDF Converter based on wkhtmltopdf for PHP

This is a yet another html to pdf converter for php. This package uses [wkhtmltopdf](https://github.com/antialize/wkhtmltopdf) as a third-party tool so `proc_*()` functions have to be enabled in your php configurations and `wkhtmltopdf` tool should be installed in your machine (You can download it from [here](http://wkhtmltopdf.org/)).

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

    echo $pdf->loadHTML('<b>Hello World</b>')->generate();

    echo $pdf->loadURL('http://www.laravel.com')->grayscale()->pageSize('A3')->orientation('Landscape')->generate();

    echo $pdf->loadHTMLFile('/home/can/index.html')->lowquality()->pageSize('A2')->generate();

## Saving the output

php-pdf uses [League\Flysystem](https://github.com/thephpleague/flysystem) to save the file to the local or remote filesystems.

### Usage

    $pdfObject->save(string $filename, League\Flysystem\AdapterInterface $adapter)

### Examples

    // Save the pdf to the local file system
    echo $pdf->loadHTML('<b>Hello World</b>')->save("invoice.pdf", new League\Flysystem\Adapter\Local(__DIR__.'/path/to/root'));

    // Save to AWS S3
    $client = S3Client::factory([
        'key'    => '[your key]',
        'secret' => '[your secret]',
    ]);
    echo $pdf->loadHTML('<b>Hello World</b>')->save("invoice.pdf", new League\Flysystem\Adapter\AwsS3($client, 'bucket-name', 'optional-prefix'));

    // Save to FTP
    $ftpConf = [
        'host' => 'ftp.example.com',
        'username' => 'username',
        'password' => 'password',

        /** optional config settings */
        'port' => 21,
        'root' => '/path/to/root',
        'passive' => true,
        'ssl' => true,
        'timeout' => 30,
    ];
    echo $pdf->loadHTML('<b>Hello World</b>')->save("invoice.pdf", new League\Flysystem\Adapter\Ftp($ftpConf));

Please see all the available adapters on the [League\Flysystem](https://github.com/thephpleague/flysystem)'s documentation

## Documentation

You can see all the available methods in the full [documentation](https://github.com/cangelis/php-pdf/blob/master/DOCUMENTATION.md) file

## Contribution

Feel free to contribute!

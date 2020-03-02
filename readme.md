# FilePreview

Generate preview images for file types.

## Installation with Composer

```composer require mykemeynell/filepreview```

## Usage

```php
$fileName = '/path/to/file.pdf';
$preview = mykemeynell\FilePreview\FilePreview::fromPath($fileName);

echo $preview->preview()->stream();
```

```FilePreview::preview()```

Actions the conversion and prepares headers for output.

```FilePreview::stream()``` 

Sets appropriate headers prepared in ```FilePreview::stream()``` and outputs 
the content.

## Custom Handlers

If you wish to change how MIME types are handled, then you can use the 
```FilePreview::addHandler($mime, $handler)``` method.

```$handler``` can accept two parameters

* [Parameter 0] Instance of ```FilePreview```.
* [Parameter 1] Instance of ```mykemeynell\FilePreview\FileSystem\File::class``` 
relating to the given file path.


```php
FilePreview::addHandler('application/pdf', function ($preview, $file) {
    // Custom handler code.
    return $preview;
});
```

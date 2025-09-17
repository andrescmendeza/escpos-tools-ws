ESC/POS command-line tools [![Build Status](https://travis-ci.org/receipt-print-hq/escpos-tools.svg?branch=master)](https://travis-ci.org/receipt-print-hq/escpos-tools)
--------------


## Project additions in this fork

This fork adds a `Dockerfile` to simplify running the tools and exposing the HTML conversion as a web service. You can build and run the project using Docker with the following commands:

```sh
# Build the Docker image
docker build -t escpos-tools-ws .

# Run the container (exposes port 8080)
docker run --rm --name escpos-tools-ws -p 8080:8080 escpos-tools-ws
```

After running the container, open your browser and go to `http://localhost:8080/output.html` to view the generated HTML from the sample ESC/POS file.

Other improvements include code quality enhancements based on Sonar recommendations.

This repository provides command-line utilities for extracting information from
binary ESC/POS data. ESC/POS is a page description language that is commonly
used for receipt printing.

Currently we have a prototype ESC/POS parser, which can extract the commands
contained in printable ESC/POS binary data, and render them to various formats.

### Example: Calling the web service from JavaScript

You can call the web service from your application using jQuery as follows:

```js
await $.post('http://localhost:8080/esc2html_service.php', {
	esc: b64 ? data : base64EncodeUnicode(dataAux),
	width: width
}).done(function (response) {
	displayEscPosLabel(response);
});
```

## Quick start

This project is requires:

- PHP 5.6 or better
- The `mbstring` and `imagick` extensions
- [composer](https://getcomposer.org/)

To install from source:

```bash
git clone https://github.com/receipt-print-hq/escpos-tools
cd escpos-tools
composer install
```

## Included utilities

### esc2text

`esc2text` extracts text and line breaks from binary ESC/POS files.

- [esc2text documentation](doc/esc2text.md)

### esc2html

`esc2html` converts binary ESC/POS files to HTML.

- [esc2html documentation](doc/esc2html.md)

### escimages

`escimages` extracts graphics from binary ESC/POS files in PBM and PNG format.

- [escimages documentation](doc/escimages.md)

## Contribute

- [CONTRIBUTING.md](CONTRIBUTING.md)

## Licensing

This project is licensed under the MIT License. See [LICENSE.md](LICENSE.md) for details.

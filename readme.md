# Fakturoid Writer

[![Build Status](https://travis-ci.org/keboola/fakturoid-writer.svg?branch=master)](https://travis-ci.org/keboola/fakturoid-writer)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/keboola/fakturoid-writer/blob/master/LICENSE.md)

Custom Docker application for pushing invoicing data to Fakturoid.

## Configuration

### Sample

```json
{
    "parameters": {
        "email": "your@email.com",
        "#token": "token",
        "slug": "slug"
    }
}
```
*Note: When copying to UI, select only content of `parameters` section

### Description of `parameters`

- `email`: Email
- `#token`: Token. Encrypted.
- `slug`: Slug; Account name

## Input

Two files at `/data/in/tables` path:

- `invoice.csv`
- `invoice-items.csv`

### `invoice.csv`

Required fields:

- `subject_id` (*Fakturoid*): An ID of contact
- `fwr_id`: Invoice ID (only in terms of this file)

Check **Invoices** section at Fakturoid API docs:
[http://docs.fakturoid.apiary.io/#reference/invoices/novy-kontakt](http://docs.fakturoid.apiary.io/#reference/invoices/novy-kontakt)

### `invoice-items.csv`

Required fields:

- `name` (*Fakturoid*): Description of item
- `quantity` (*Fakturoid*): Quantity
- `unit_price` (*Fakturoid*): Unit price for item
- `vat_rate` (*Fakturoid*): VAT rate (`0` for non payers)
- `fwr_invoice_id`: Invoice ID (foreign key); `fwr_id` field value from `invoice.csv` file

Check **Lines** section at Fakturoid API docs:
[http://docs.fakturoid.apiary.io/#reference/lines/novy-kontakt](http://docs.fakturoid.apiary.io/#reference/lines/novy-kontakt)

## Output

TBD

## Development

Requirements:

- Docker Engine and Docker Compose (combination which support `docker-compose.yml` v2)

Application is prepared for run in container, you can start development same way:

1. Clone this repository and go to directory with clone
2. Build services: `docker-compose build`
3. Run tests `docker-compose run --rm app-tests` (runs `./tests.sh` script)

After seeing all tests green, continue:

1. Run service: `docker-compose run --rm app` (starts container with `bash`)
2. Write tests and code
3. Run tests: `./tests.sh`

To simulate real run:

1. Create data dir: `mkdir -p data`
2. Follow configuration sample and create `config.json` file and place it to your data directory (`data/config.json`)
3. Prepare input data
4. Simulate real run (with entrypoint command): `php ./src/app.php run ./data`

### Tests

- all in one: `./tests.sh`
- or separately, just check `tests.sh` file contents

## License

MIT. See [license file.](/license.md)

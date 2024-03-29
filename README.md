# Fakturoid Writer

[![Build Status](https://travis-ci.org/keboola/fakturoid-writer.svg?branch=master)](https://travis-ci.org/keboola/fakturoid-writer)
[![Code Climate](https://codeclimate.com/github/keboola/fakturoid-writer/badges/gpa.svg)](https://codeclimate.com/github/keboola/fakturoid-writer)
[![Test Coverage](https://codeclimate.com/github/keboola/fakturoid-writer/badges/coverage.svg)](https://codeclimate.com/github/keboola/fakturoid-writer/coverage)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/keboola/fakturoid-writer/blob/master/LICENSE.md)

Custom Docker application for pushing invoicing data to Fakturoid.

## Configuration

### Sample

```json
{
    "parameters": {
        "email": "your@email.com",
        "#token": "token",
        "slug": "slug",
        "order": "asc"
    }
}
```
**Note: When copying to UI, select only content of `parameters` section.**

### Description of `parameters`

- `email`: Email
- `#token`: Token. Encrypted.
- `slug`: Slug; Account name
- `order`: If order items `desc` or `asc` by `fwr_order` field (default to `asc`)

## Input

Two files at `/data/in/tables` path:

- `invoice.csv`
- `invoice-items.csv`

### `invoice.csv`

Sample:

|...|fwr_id|fwr_order|subject_id|...|
|---|---|---|---|---|
|...|11|1|1001|...|
|...|12|2|1002|...|

Required fields:

- `subject_id` (*Fakturoid*): An ID of contact
- `fwr_id`: Invoice ID (only in terms of this file)
- `fwr_order`: Order (only in terms of this file). Invoices will be sorted and send to API in by this field.

Check **Invoices** section at Fakturoid API docs:
[http://docs.fakturoid.apiary.io/#reference/invoices/novy-kontakt](http://docs.fakturoid.apiary.io/#reference/invoices/novy-kontakt)

### `invoice-items.csv`

Sample:

|...|fwr_invoice_id|name|quantity|unit_price|vat_rate|...|
|---|---|---|---|---|---|---|
|...|11|Item 1|1|100|0|...|
|...|12|Item 1|1|100|0|...|
|...|12|Item 2|3|50|0|...|

Required fields:

- `fwr_invoice_id`: Invoice ID (foreign key); `fwr_id` field value from `invoice.csv` file
- `name` (*Fakturoid*): Description of item
- `quantity` (*Fakturoid*): Quantity
- `unit_price` (*Fakturoid*): Unit price for item
- `vat_rate` (*Fakturoid*): VAT rate (`0` for non payers)

Check **Lines** section at Fakturoid API docs:
[http://docs.fakturoid.apiary.io/#reference/lines/novy-kontakt](http://docs.fakturoid.apiary.io/#reference/lines/novy-kontakt)

## Output

One file at `/data/out/tables` path is exported. Each row contains full response from Fakturoid.

Sample:

|data|
|---|
|{""id"":3701,""subject_id"":1001,""items"":[{""name"":""Item 1""}]}|
|...|

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

MIT licensed, see [LICENSE](./LICENSE) file.

# Fakturoid Writer

[![Build Status](https://travis-ci.org/keboola/fakturoid-writer.svg?branch=master)](https://travis-ci.org/keboola/fakturoid-writer)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/keboola/fakturoid-writer/blob/master/LICENSE.md)

Custom Docker application for pushing invoicing data to Fakturoid.

## Configuration

Sample configuration and its description can be found [here](/config.md).

## Input

Two files at `/data/in/tables` path:

- `invoice.csv`
- `invoice-items.csv`

### `invoice.csv` required fields

- `subject_id` (*Fakturoid*): An ID of contact
- `fwr_id`: Invoice ID (only in terms of this file)

### `invoice-items.csv` required fields

- `name` (*Fakturoid*): Description of item
- `quantity` (*Fakturoid*): Quantity
- `unit_price` (*Fakturoid*): Unit price for item
- `vat_rate` (*Fakturoid*): VAR rate (`0` for non payers)
- `fwr_invoice_id`: Invoice ID (foreign key) from `invoice.csv` file

## Output

TBD

## Development

Requirements:

- Docker Engine and Docker Compose (combination which support `docker-compose.yml` v2)

TBD

### Tests

TBD

## License

MIT. See [license file.](/license.md)

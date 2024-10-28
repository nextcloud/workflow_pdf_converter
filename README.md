<!--
  - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Nextcloud PDF Converter app

This app lets Nextcloud automatically convert documents to PDF. By utilizing the workflow engine it allows Nextcloud administrators to define rules upon which various documents are enqueued to be converted to PDF. Eventually, the conversion happens in a background job by feeding the source file to the found or specific `libreoffice` or `openoffice` binary. Depending on the selected behaviour the source file can either be kept or deleted and the resulting PDFs can either be preserved by increasing a number added to the filename or overwritten.

The conversion job is being created when a file was created or updated and also when a system tag was assigned.

Learn more about workflows on https://nextcloud.com/workflow

## Requirements

LibreOffice must be installed on the server and the binary must be either detectable by Nextcloud or specified in the config.php as `preview_libreoffice_path` (cf. the sample config).

## Limitations

This app does not work with either encryption method.

Since LibreOffice is used for conversion, its import filters decide the possibility and quality of conversion. Essentially, Office formats, plain text documents, HTML files but also graphics can be converted. Due to a high number of very custom mime types, by default we feed anything to LibreOffice apart from Audio and Video files.

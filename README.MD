# Unico Hiring Challenge - ETL module
![PHP Version](https://img.shields.io/badge/PHP%20Version-8.0-informational)
![Coding Style](https://img.shields.io/badge/Coding%20Style-PSR--12-yellow)
![Psalm Static Analysis](https://github.com/NickStarlight/unico-hiring-challenge-etl/actions/workflows/Psalm.yaml/badge.svg)
![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-lightgrey.svg)

## About
This module contains the first half of the Unico Hiring Challenge that is described as following:

* Store the data provided by the City of São
Paulo in a relational database that you judge
appropriate
* The solution must contain a script to import the data
from the file "DEINFO_AB_FEIRASLIVRES_2014.csv" to the
relational bank.

## Requeriments
* Docker
* The server part of the project up and running,.

## Usage
Currently only the "DEINFO_AB_FEIRASLIVRES_2014.csv" file is available for ETL, you can start the process using the CLI commands:

1. Configure your environment using the `.env.example` file as template by creating an `.env` file. This should point to an up and running PostgreSQL instance(usually set with the server counterpart of the project).
The defaults included should work out of the box with the server-side.

2. Build the Docker image:

```bash
docker build -t unico/fair-etl .
```

3. Run the script:
```bash
docker run -it -v $(pwd):/usr/src/etl --rm --name unicoetl unico/fair-etl
``` 
You'll only be prompted one question regarding which file you wan't import into the database, the script will download and parse the file automatically.

    Note: This command is simply starting the Docker Image we created in the previous step and binding the current directory as a volume in Docker for the script to be able to write error logs to log.txt
    The --rm parameter is a mere convenience, this deletes the container after the execution.

## Logging
Errors are automatically logged to `log.txt`

## Database structure
![UML](https://i.imgur.com/ybOpylH.png)

The original dataset has been broken down in several relationships in order to maintain atomicity.
In the future, complex geometric checks can be easily implemented, like for example, checking if given `fair_address` coordinates are within the boundaries of `district` or `census_area` geometries.
The migrations for the database are on the server-side counterpart of this project.

The database structure also leverages the PostgreSQL PostGIS extension, allowing easy geo data analysis.
![Database Geo](https://i.imgur.com/jEdfV9i.png)

## Testing
This repository offers no unit/integration tests due time constraints.

## License
This work is licensed under a
[Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License][cc-by-nc-sa].

[![CC BY-NC-SA 4.0][cc-by-nc-sa-image]][cc-by-nc-sa]

[cc-by-nc-sa]: http://creativecommons.org/licenses/by-nc-sa/4.0/
[cc-by-nc-sa-image]: https://licensebuttons.net/l/by-nc-sa/4.0/88x31.png
[cc-by-nc-sa-shield]: https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-lightgrey.svg

<?php

declare(strict_types=1);

namespace UnicoETL\Parsers;

use Exception;
use Iterator;
use League\Csv\Reader;
use PDO;
use PDOException;
use UnicoETL\Helpers\Logger;
use UnicoETL\Helpers\PG;

/**
 * Class Fair2014
 * Implements the logic for parsing and inserting on the
 * database the data from the `DEINFO_AB_FEIRASLIVRES_2014.csv` file.
 * 
 * @package UnicoETL
 * @author Nick Moraes <contato@nickgomes.dev>
 * @version 1.0
 * @access public
 * @license https://creativecommons.org/licenses/by-nc/3.0/
 */
final class Fair2014 implements Parser
{
    /**
     * A pointer containing an iterator for
     * the parsed CSV records.
     * 
     * @var Iterator
     */
    protected Iterator $csvRecords;

    /** 
     * A pointer for a PDO instance.
     * 
     * @var PDO
     */
    protected PDO $db;

    /**
     * Initializes the Parser.
     * 
     * @param mixed $content The .csv raw text contents
     * @return void
     */
    public function __construct(mixed $content)
    {
        $this->parse(content: $content);
        $this->db = (new PG())->getDB();
    }

    /**
     * Parse the .csv content from provided file content.
     * 
     * @param mixed $content The .csv raw text content
     * @return void
     */
    public final function parse(mixed $content): void
    {
        $csvParser = Reader::createFromString(content: $content);
        $csvParser->setHeaderOffset(offset: 0);
        $records = $csvParser->getRecords();

        $this->csvRecords = $records;
    }

    /**
     * Insert the parsed content into the database.
     * 
     * @return void
     */
    public final function output(): void
    {
        $fairList = iterator_to_array(iterator: $this->csvRecords, preserve_keys: true);
        $censusAreas = $this->extractUniqueKeys(array: $fairList, key: 'AREAP');
        $censusSectors = $this->extractUniqueKeys(array: $fairList, key: 'SETCENS', keysToReturn: ['AREAP']);
        $boroughs = $this->extractUniqueKeys(array: $fairList, key: 'SUBPREFE', keysToReturn: ['CODSUBPREF', 'REGIAO5', 'REGIAO8']);
        $districts = $this->extractUniqueKeys(array: $fairList, key: 'DISTRITO', keysToReturn: ['CODDIST', 'SUBPREFE']);

        try {
            $this->db->beginTransaction();

            $this->insertCensusAreasInDB(values: $censusAreas);
            $this->insertCensusSectorsInDB(values: $censusSectors);
            $this->insertBoroughsInDB(values: $boroughs);
            $this->insertDistrictsInDB(values: $districts);
            $this->insertFairsInDB(values: $fairList);

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();

            Logger::Error($e);
            throw new Exception('Failed to insert records in the database, check the log file for more details.');
        }
    }

    /** 
     * Extract a list of unique values from an array containing arrays of key-value pairs.
     * Used for removing duplicates from a multi-dimensional array.
     * 
     * @param array $array The lookup array
     * @param string $key The key to extract unique rows
     * @param array $keysToReturn Which keys should be returned for each row
     * 
     * @return array
     */
    protected final function extractUniqueKeys($array, $key, $keysToReturn = []): array
    {
        /** Make sure the 'comparison key' is also one of the returned ones */
        array_push($keysToReturn, $key);
        $selectedKeys = array_map(function ($e) use ($keysToReturn) {
            return array_intersect_key($e, array_flip($keysToReturn));
        }, $array);

        return array_reverse(array_values(array_column(
            array_reverse($selectedKeys),
            null,
            $key
        )));
    }

    /**
     * Returns a trimmed version of a string or null if empty.
     * 
     * @param string $string The string to be trimmed
     * @param int $length The maximum length to from the string
     * 
     * @return string|null
     */
    protected final static function safeString(string|null $string, int $length): string|null
    {
        if ($string === '' || $string === null) {
            return null;
        }

        return substr($string, 0, $length);
    }

    /**
     * Insert a list of Census Areas in the database.
     * 
     * @param array $values A list containing a key-value pair array for each area.
     * 
     * @return void
     */
    protected final function insertCensusAreasInDB(array $values): void
    {
        $query = <<<SQL
            INSERT INTO census_areas (code, created_at, updated_at) VALUES (:code, NOW(), NOW())
        SQL;
        $prepStat = $this->db->prepare($query);

        foreach ($values as $area) {
            $prepStat->bindValue(':code', $area['AREAP'], PDO::PARAM_STR);
            $prepStat->execute();
        }
    }

    /**
     * Insert a list of Census Secots in the database.
     * 
     * @param array $values A list containing a key-value pair array for each sector.
     * 
     * @return void
     */
    protected final function insertCensusSectorsInDB(array $values): void
    {
        $query = <<<SQL
            INSERT INTO census_sectors (code, census_area_id, created_at, updated_at) 
            VALUES (:code, (SELECT id FROM census_areas WHERE code = :census_area), NOW(), NOW())
        SQL;
        $prepStat = $this->db->prepare($query);

        foreach ($values as $sector) {
            $prepStat->bindValue(':code', $sector['SETCENS'], PDO::PARAM_STR);
            $prepStat->bindValue(':census_area', $sector['AREAP'], PDO::PARAM_STR);
            $prepStat->execute();
        }
    }

    /**
     * Insert a list of Boroughs in the database.
     * 
     * @param array $values A list containing a key-value pair array for each borough.
     * 
     * @return void
     */
    protected final function insertBoroughsInDB(array $values): void
    {
        $query = <<<SQL
            INSERT INTO boroughs (name, smdu_code, octave_region_name, quinary_region_name, created_at, updated_at) 
            VALUES (:name, :smdu_code, :octave_region_name, :quinary_region_name, NOW(), NOW())
        SQL;
        $prepStat = $this->db->prepare($query);

        foreach ($values as $borough) {
            $prepStat->bindValue(':name', $borough['SUBPREFE'], PDO::PARAM_STR);
            $prepStat->bindValue(':smdu_code', $borough['CODSUBPREF'], PDO::PARAM_STR);
            $prepStat->bindValue(':quinary_region_name', $borough['REGIAO5'], PDO::PARAM_STR);
            $prepStat->bindValue(':octave_region_name', $borough['REGIAO8'], PDO::PARAM_STR);
            $prepStat->execute();
        }
    }

    /**
     * Insert a list of Districts in the database.
     * 
     * @param array $values A list containing a key-value pair array for each district.
     * 
     * @return void
     */
    protected final function insertDistrictsInDB(array $values): void
    {
        $query = <<<SQL
            INSERT INTO districts (name, ibge_code, borough_id, created_at, updated_at) 
            VALUES (:name, :ibge_code, (SELECT id FROM boroughs WHERE name = :borough), NOW(), NOW())
        SQL;
        $prepStat = $this->db->prepare($query);

        foreach ($values as $district) {
            $prepStat->bindValue(':name', $district['DISTRITO'], PDO::PARAM_STR);
            $prepStat->bindValue(':ibge_code', $district['CODDIST'], PDO::PARAM_STR);
            $prepStat->bindValue(':borough', $district['SUBPREFE'], PDO::PARAM_STR);
            $prepStat->execute();
        }
    }

    /**
     * Insert a list of Fairs and their addresses in the database.
     * 
     * @param array $values A list containing a key-value pair array for each fair.
     * 
     * @return void
     */
    protected final function insertFairsInDB(array $values): void
    {
        $query = <<<SQL
        WITH address_id_inserted AS (
            INSERT INTO fair_addresses
            (
                number,
                street,
                neighborhood,
                reference_point,
                coordinates,
                district_id,
                census_area_id,
                updated_at,
                created_at
            )
            VALUES
            (
                :number,
                :street,
                :neighborhood,
                :reference_point,
                (ST_SetSRID(ST_MakePoint(:longitude, :latitude), 4326)),
                (
                    SELECT id
                    FROM   districts
                    WHERE  name = :district
                ),
                (
                    SELECT id
                    FROM   census_areas
                    WHERE  code = :census_area
                ),
                NOW(),
                NOW()
            )
            RETURNING id
        )

        INSERT INTO fairs
        (
            name,
            pmsp_code,
            address_id,
            updated_at,
            created_at
        )
        VALUES
        (
            :name,
            :pmsp_code,
            (SELECT id FROM address_id_inserted),
            NOW(),
            NOW()
        )
        SQL;
        $prepStat = $this->db->prepare($query);

        foreach ($values as $district) {
            $safeNumber = intval($district['NUMERO']);
            $safeLat = intval($district['LAT']) / 1000000;
            $safeLon = intval($district['LONG']) / 1000000;

            $prepStat->bindValue(':name', self::safeString($district['NOME_FEIRA'], 30), PDO::PARAM_STR);
            $prepStat->bindValue(':pmsp_code', self::safeString($district['REGISTRO'], 6), PDO::PARAM_STR);
            $prepStat->bindValue(':number', $safeNumber === 0 ? null : $safeNumber, PDO::PARAM_INT);
            $prepStat->bindValue(':street', self::safeString($district['LOGRADOURO'], 34), PDO::PARAM_STR);
            $prepStat->bindValue(':neighborhood', self::safeString($district['BAIRRO'], 20), PDO::PARAM_STR);
            $prepStat->bindValue(':reference_point', self::safeString($district['REFERENCIA'], 24), PDO::PARAM_STR);
            $prepStat->bindValue(':longitude', $safeLon, PDO::PARAM_STR);
            $prepStat->bindValue(':latitude', $safeLat, PDO::PARAM_STR);
            $prepStat->bindValue(':district', $district['DISTRITO'], PDO::PARAM_STR);
            $prepStat->bindValue(':census_area', $district['AREAP'], PDO::PARAM_STR);
            $prepStat->execute();
        }
    }
}

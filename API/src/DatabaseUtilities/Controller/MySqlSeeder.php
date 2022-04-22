<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller;

use Exception;

//seed the db with pre-defined data
/**
 * Utilities to seed the database
 *
 * @codeCoverageIgnore
 */
class MySqlSeeder
{

    //seed specific data-sets 
    //the array should be an array of strings. Each string 
    static public function seed(\PDO $pdo, array $strings): void
    {

        //check if the requested seeds exists
        foreach ($strings as $s) {
            if (!array_key_exists($s, self::SEEDS)) {
                throw new Exception("Seed don't exists");
            }
        }

        //seed the db via SQL INSERT INTO
        foreach ($strings as $s) {
            $pdo->exec(self::SEEDS[$s]);
        }
    }

    //name-SQL_Statement-pair array 
    private const SEEDS = [ //TODO: move seeds to seed-files maybe.

        'roles' =>  '
            INSERT INTO role
                (role_id, name, role_read, role_write, role_delete, user_read, user_write, user_delete)
            VALUES 
                (0,"admin",true,true,true,true,true,true),
                (1,"user",false,false,false,true,false,false);
        ',

        'admin' =>  '
            INSERT INTO user
                (email, name, postcode, city, phone, hashed_pass, verified, role_id)
            VALUES 
                ("admin@mail.de","admin","00000","admintown","015937839",1,true,0);
        '
    ]; //TODO change admin pass_hash
}

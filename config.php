<?php

// Google Gemini API Key - set your key here or use environment variable
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'AIzaSyDWa4Vlo15I3BsMQmcuUsE8r2QXT49Nxnw');

class config

{

  private static $pdo = null;



  public static function getConnexion()

  {

    if (!isset(self::$pdo)) {

      try {

        self::$pdo = new PDO(

          'mysql:host=localhost;dbname=perfran;charset=utf8',

          'root',

          '',

          [

            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC

          ]

        );

      } catch (Exception $e) {

        die('Error: ' . $e->getMessage());

      }

    }

    return self::$pdo;

  }

}
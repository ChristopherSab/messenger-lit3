<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Kreait\Firebase\Database;

//Controller is for testing Firebase Database connection

class TestController extends AbstractController
{

    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }


    /**
     * @Route("/query", name="query")
     */
    public function query()
    {

        $reference = $this->database->getReference('/email');
        $snapshot = $reference->getSnapshot();
        $value = $snapshot->getValue();
        dd($value);

    }







}

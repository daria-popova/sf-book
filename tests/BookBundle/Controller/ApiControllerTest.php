<?php

namespace Tests\BookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use BookBundle\Entity\Book;

class ApiControllerTest extends WebTestCase
{
    private $correctApiKey = 'f3abfd8063e82d244f9315ec7daa2737';

    public function testList()
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/books');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('API key is missing', $client->getResponse()->getContent());

        $client->request('GET', '/api/v1/books?apiKey=123');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('API key is incorrect', $client->getResponse()->getContent());

        $client->request('GET', '/api/v1/books?apiKey=' . $this->correctApiKey);
        $this->assertEquals('200', $client->getResponse()->getStatusCode());
    }

    public function testAddConstraints()
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/books/add');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('API key is missing', $client->getResponse()->getContent());

        $client->request('POST', '/api/v1/books/add?apiKey=123');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('API key is incorrect', $client->getResponse()->getContent());

        $client->request('POST', '/api/v1/books/edit/1');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('API key is missing', $client->getResponse()->getContent());

        $client->request('POST', '/api/v1/books/edit/1?apiKey=123');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('API key is incorrect', $client->getResponse()->getContent());

        $fields = array(
            'title' => 'test book',
            'author' => 'test author',
            'readDate' => '2011-05-11',
            'isDownloadAllowed' => false
        );

        $fieldsTest = $fields;
        unset($fieldsTest['title']);
        $client->request('POST', '/api/v1/books/add?apiKey=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains("Field 'title' is required", $client->getResponse()->getContent());

        $fieldsTest = $fields;
        $fieldsTest['title'] = '';
        $client->request('POST', '/api/v1/books/add?apiKey=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains("Field 'title' must be a non-empty string", $client->getResponse()->getContent());

        $fieldsTest = $fields;
        unset($fieldsTest['readDate']);
        $client->request('POST', '/api/v1/books/add?apiKey=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains("Field 'readDate' is required", $client->getResponse()->getContent());

        $fieldsTest = $fields;
        $fieldsTest['readDate'] = 'qwerty';
        $client->request('POST', '/api/v1/books/add?apiKey=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains("Field 'readDate' must be a valid date", $client->getResponse()->getContent());
    }

    public function testAddAndEdit()
    {
        $client = static::createClient();

        $fields = array(
            'title' => 'test book ' . uniqid(),
            'author' => 'test author',
            'readDate' => '2011-05-11',
            'isDownloadAllowed' => false
        );
        $client->request('POST', '/api/v1/books/add?apiKey=' . $this->correctApiKey, $fields);
        $this->assertEquals('200', $client->getResponse()->getStatusCode());
        $this->assertContains('ok', $client->getResponse()->getContent());
        $createdBook = json_decode($client->getResponse()->getContent());
        $id = $createdBook->message->id;
        $this->assertNotNull($id);


        $client->request('GET', '/api/v1/books?apiKey=' . $this->correctApiKey);
        $this->assertContains($fields['title'], $client->getResponse()->getContent());

        $oldTitle = $fields['title'];
        $fields['title'] = 'edited title ' . uniqid();

        $client->request('POST', '/api/v1/books/edit/'.$id.'?apiKey=' . $this->correctApiKey, $fields);
        $this->assertEquals('200', $client->getResponse()->getStatusCode());
        $this->assertContains('ok', $client->getResponse()->getContent());

        $client->request('GET', '/api/v1/books?apiKey=' . $this->correctApiKey);
        $this->assertContains($fields['title'], $client->getResponse()->getContent());
        $this->assertNotContains($oldTitle, $client->getResponse()->getContent());


        $kernel = self::bootKernel();
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $book = $em->find(Book::class, $id);
        if ($book) {
            $result = $em->remove($book);
            $em->flush();
        }
    }
}

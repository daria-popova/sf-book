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
        $this->assertContains('Token is missing', $client->getResponse()->getContent());

        $client->request('GET', '/api/v1/books?token=123');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('Invalid token', $client->getResponse()->getContent());

        $client->request('GET', '/api/v1/books?token=' . $this->correctApiKey);
        $this->assertEquals('200', $client->getResponse()->getStatusCode());
    }

    public function testAddConstraints()
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/books/add');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('Token is missing', $client->getResponse()->getContent());

        $client->request('POST', '/api/v1/books/add?token=123');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('Invalid token', $client->getResponse()->getContent());

        $client->request('POST', '/api/v1/books/edit/1');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('Token is missing', $client->getResponse()->getContent());

        $client->request('POST', '/api/v1/books/edit/1?token=123');
        $this->assertEquals('403', $client->getResponse()->getStatusCode());
        $this->assertContains('Invalid token', $client->getResponse()->getContent());

        $fields = [
            'title' => 'test book',
            'author' => 'test author',
            'readDate' => '2011-05-11',
            'isDownloadAllowed' => false
        ];

        $fieldsTest = $fields;
        unset($fieldsTest['title']);
        $client->request('POST', '/api/v1/books/add?token=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains('title', $client->getResponse()->getContent());
        $this->assertContains('This value should not be blank', $client->getResponse()->getContent());

        $fieldsTest = $fields;
        unset($fieldsTest['isDownloadAllowed']);
        $client->request('POST', '/api/v1/books/add?token=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains('isDownloadAllowed', $client->getResponse()->getContent());
        $this->assertContains('This value should not be null', $client->getResponse()->getContent());

        $fieldsTest = $fields;
        $fieldsTest['title'] = '';
        $client->request('POST', '/api/v1/books/add?token=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains('title', $client->getResponse()->getContent());
        $this->assertContains('This value should not be blank', $client->getResponse()->getContent());

        $fieldsTest = $fields;
        unset($fieldsTest['readDate']);
        $client->request('POST', '/api/v1/books/add?token=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains('readDate', $client->getResponse()->getContent());
        $this->assertContains('This value should not be blank', $client->getResponse()->getContent());

        $fieldsTest = $fields;
        $fieldsTest['readDate'] = 'qwerty';
        $client->request('POST', '/api/v1/books/add?token=' . $this->correctApiKey, $fieldsTest);
        $this->assertEquals('400', $client->getResponse()->getStatusCode());
        $this->assertContains('readDate', $client->getResponse()->getContent());
        $this->assertContains("This value is not a valid datetime", $client->getResponse()->getContent());
    }

    public function testAddAndEdit()
    {
        $client = static::createClient();

        $fields = [
            'title' => 'test book ' . uniqid(),
            'author' => 'test author',
            'readDate' => '2011-05-11',
            'isDownloadAllowed' => false
        ];
        $client->request('POST', '/api/v1/books/add?token=' . $this->correctApiKey, $fields);
        $this->assertEquals('201', $client->getResponse()->getStatusCode());
        $createdBook = json_decode($client->getResponse()->getContent());
        $this->assertEquals($createdBook->success, true);
        $this->assertNotNull($createdBook->id);


        $client->request('GET', '/api/v1/books?token=' . $this->correctApiKey);
        $this->assertContains($fields['title'], $client->getResponse()->getContent());

        $oldTitle = $fields['title'];
        $fields['title'] = 'edited title ' . uniqid();

        $client->request('POST', '/api/v1/books/edit/'.$createdBook->id.'?token=' . $this->correctApiKey, $fields);
        $this->assertEquals('200', $client->getResponse()->getStatusCode());
        $editedBook = json_decode($client->getResponse()->getContent());
        $this->assertEquals($editedBook->success, true);

        $client->request('GET', '/api/v1/books?token=' . $this->correctApiKey);
        $this->assertContains($fields['title'], $client->getResponse()->getContent());
        $this->assertNotContains($oldTitle, $client->getResponse()->getContent());


        $kernel = self::bootKernel();
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $book = $em->find(Book::class, $createdBook->id);
        if ($book) {
            $em->remove($book);
            $em->flush();
        }
    }
}

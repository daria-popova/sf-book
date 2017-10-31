<?php

namespace BookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\ORM\EntityManagerInterface;
use BookBundle\Entity\Book;

class ApiController extends Controller
{
    /**
     * @Route("/api/v1/books", name="api_list")
     * @Method("GET")
     */
    public function listController(Request $request)
    {
        $serializer = $this->container->get('jms_serializer');
        $apiKeyErrors = $this->checkApiKeyErrors($request);
        if (!empty($apiKeyErrors)) {
            return $this->jsonResponse('error', $apiKeyErrors, 403);
        }

        $cacheTime = $this->container->getParameter('default_cache_time');
        $repository = $this->getDoctrine()->getRepository(Book::class);

        $query = $repository->createQueryBuilder('b')
            ->orderBy('b.readDate', 'DESC')
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->setResultCacheLifetime($cacheTime)
            ->setResultCacheId('list_desc');

        $books = $query->getResult();

        foreach ($books as $book) {
            if ($book->getCover()) {
                $book->setCover(
                    $request->getScheme() . '://' .
                    $request->getHost() .
                    $this->container->getParameter('upload_directory') . '/' .
                    $book->getCover()
                );
            }

            if ($book->getSource() && $book->getIsDownloadAllowed()) {
                $book->setSource(
                    $request->getScheme() . '://' .
                    $request->getHost() .
                    $this->container->getParameter('upload_directory') . '/' .
                    $book->getSource()
                );
            } else {
                $book->setSource(null);
            }
        }

        $jsonContent = $serializer->serialize($books, 'json');

        return new Response($jsonContent, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/api/v1/books/add", name="api_add")
     * @Method("POST")
     */
    public function createController(Request $request)
    {
        $apiKeyErrors = $this->checkApiKeyErrors($request);
        if (!empty($apiKeyErrors)) {
            return $this->jsonResponse('error', $apiKeyErrors, 403);
        }

        return $this->createOrEditAction(null, $request);
    }


    /**
     * @Route("/api/v1/books/edit/{id}", name="api_edit", requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function editController($id, Request $request)
    {
        $apiKeyErrors = $this->checkApiKeyErrors($request);
        if (!empty($apiKeyErrors)) {
            return $this->jsonResponse('error', $apiKeyErrors, 403);
        }

        return $this->createOrEditAction($id, $request);
    }


    private function createOrEditAction($id, Request $request)
    {
        $fields = $request->request->all();
        $serializer = $this->container->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $requiredFields = ['title', 'author', 'readDate', 'isDownloadAllowed'];
        $errors = [];

        foreach ($requiredFields as $requiredField) {
            if (!isset($fields[$requiredField]) || is_null($fields[$requiredField])) {
                $errors[] = 'Field \'' . $requiredField . '\' is required';
            }
        }

        if (!empty($errors)) {
            return $this->jsonResponse('error', $errors, 400);
        }

        if (!$id) {
            $book = new Book();
        } else {
            $book = $em->find(Book::class, $id);
            if (!$book) {
                return $this->jsonResponse('error', ['Book with id ' . $id . ' not found'], 404);
            }
        }

        $book->setTitle($fields["title"]);
        $book->setAuthor($fields["author"]);
        $book->setReadDate(\DateTime::createFromFormat('Y-m-d', $fields["readDate"]));
        $book->setIsDownloadAllowed($fields["isDownloadAllowed"]);

        $validator = $this->get('validator');
        $errors = $validator->validate($book);

        if ($errors->count()) {
            return $this->jsonResponse('error', $errors, 400);
        }

        $em->persist($book);
        $em->flush();

        return $this->jsonResponse('ok', ['id' => $book->getId()], 200);
    }


    private function checkApiKeyErrors(Request $request)
    {
        if (!$request->query->has('apiKey')) {
            return ['API key is missing'];
        }

        $userApiKey = $request->query->get('apiKey');
        $apiKey = $this->container->getParameter('api_key');
        if ($apiKey !== $userApiKey) {
            return ['API key is incorrect'];
        }

        return null;
    }

    private function jsonResponse($status, $message, $statusCode)
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response(
            $serializer->serialize(
                [
                    'status' => $status,
                    'message' => $message
                ],
                'json'
            ),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }
}

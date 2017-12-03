<?php

namespace BookBundle\Controller;

use BookBundle\Controller\ApiModel\BookListResponse;
use BookBundle\Controller\ApiModel\EditSuccessResponse;
use BookBundle\Controller\ApiModel\ErrorResponse;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\ORM\EntityManagerInterface;
use BookBundle\Entity\Book;
use Symfony\Component\Validator\Tests\Fixtures\ConstraintAValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends Controller implements TokenAuthenticatedController
{
    /**
     * @Route("/api/v1/books", name="api_list")
     * @Method("GET")
     */
    public function listController(Request $request)
    {
        $serializer = $this->container->get('jms_serializer');

        $books = $this
            ->getDoctrine()
            ->getRepository(Book::class)
            ->findAllOrderedByDateDesc();

        foreach ($books as $book) {
            $book->setCover(
                $this->getFileWebPath($book->getCover(), $request)
            );

            if ($book->getIsDownloadAllowed()) {
                $book->setSource(
                    $this->getFileWebPath($book->getSource(), $request)
                );
            } else {
                $book->setSource(null);
            }
        }

        $jsonContent = $serializer->serialize(new BookListResponse($books), 'json');

        return JsonResponse::fromJsonString($jsonContent);
    }

    /**
     * @Route("/api/v1/books/add", name="api_add")
     * @Method("POST")
     */
    public function createController(Request $request)
    {
        return $this->createOrEditAction(null, $request);
    }


    /**
     * @Route("/api/v1/books/edit/{id}", name="api_edit", requirements={"id": "\d+"})
     * @Method("POST")
     */
    public function editController(int $id, Request $request)
    {
        return $this->createOrEditAction($id, $request);
    }


    private function createOrEditAction(?int $id, Request $request)
    {
        $fields = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        if (!$id) {
            $book = new Book();
        } else {
            $book = $em->find(Book::class, $id);
            if (!$book) {
                return new JsonResponse(new ErrorResponse(['Book not found']), 404);
            }
        }

        $book->setTitle($fields["title"] ?? null);
        $book->setAuthor($fields["author"] ?? null);
        $readDate = $fields["readDate"] ?? null;
        if ($readDate) {
            $readDate = \DateTime::createFromFormat('Y-m-d', $fields["readDate"]);
        }
        $book->setReadDate($readDate);
        $book->setIsDownloadAllowed($fields["isDownloadAllowed"] ?? null);

        $validator = $this->get('validator');
        $errors = $validator->validate($book);

        if ($errors->count()) {
            return new JsonResponse(new ErrorResponse($errors), 400);
        }

        if (!$id) {
            $em->persist($book);
        }
        $em->flush();

        return new JsonResponse(
            new EditSuccessResponse($book->getId()),
            $id ? 200 : 201
        );
    }

    private function getFileWebPath(?string $relativePath, Request $request) : ?string
    {
        //too many dependencies for event listener
        if (!$relativePath) {
            return null;
        }

        return $request->getSchemeAndHttpHost() .
            $this->container->getParameter('upload_dir') .
            $relativePath;
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\User;
use AppBundle\Form\ArticleType;
use AuthHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultController extends Controller
{

    public function __construct()
    {
        $this->authHelper = new AuthHelper();
    }

    /**
     * @Route("/") 
     */
    public function redirectIndex()
    {
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/login", name="login")
     * @Method({"GET", "POST"})
     * 
     */
    public function loginAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        if ($data) {
            $userExists = $em->getRepository(User::class)->findOneBy(
                array('username' => $data['username'], 'password' => $data['password'])
            );//Search in the DDBB for users who coincide with the inputs value.
            if ($userExists) {
                $this->authHelper->login($userExists);
                return $this->redirectToRoute('index');
            } else {
                return $this->render('default/login.html.twig', array(
                    'errorMsg' => 'The user/password is incorrect'
                ));
            }
        }
        return $this->render('default/login.html.twig');
    }

    /**
     *
     * @Route("/{currentPage}/index", name="index")
     */
    public function indexAction($currentPage = 1)
    {
        $this->authHelper->isLogedIn();

        $em = $this->getDoctrine()->getManager();
        $limit = 10;
        $articles = $em->getRepository(Article::class)->getAllPers($currentPage, $limit);
        $articlesResult = $articles['paginator'];
        $articlesCompleteQuery =  $articles['query'];

        $maxPages = ceil($articles['paginator']->count() / $limit);

        return $this->render('default/index.html.twig', array(
            'articles' => $articlesResult,
            'maxPages' => $maxPages,
            'thisPage' => $currentPage,
            'all_items' => $articlesCompleteQuery
        ));
    }

    /**
     * @Route("/addArticle", name="addArticle")
     * @Method({"GET", "POST"})
     * 
     */
    public function addArticle(Request $request)
    {
        $this->authHelper->isLogedIn();
        $em = $this->getDoctrine()->getManager();

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {

            $title = $form->getData()->getTitle();
            $alreadyExists = $em->getRepository(Article::class)->findOneByTitle($title);
            if ($alreadyExists) {
                return $this->render('default/addArticle.html.twig', array(
                    "form" => $form->createView(),
                    "errorMsg" => "The title of the article is already registered, please change it"
                ));
            } else {
                /** 
                 * @var UploadedFile $file
                 */
                $file = $article->getPictureRoute();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move(
                    $this->getParameter('image_directory'),
                    $fileName
                );
                $article->setDateAdded(new \DateTime('now'));
                $article->setPictureRoute($fileName);
                $em->persist($article);
                $em->flush();
            }




            return $this->redirectToRoute('index');
        }

        return $this->render('default/addArticle.html.twig', array(
            "form" => $form->createView(),
        ));
    }

    /**
     * @Route("/article/{id}", name="article")
     * @Method({"GET", "POST"})
     * 
     */
    public function displayArticle($id)
    {
        $this->authHelper->isLogedIn();
        $esAdm = false;
        if (!empty($_SESSION['ADMIN_ID'])) {
            $esAdm = true;
        }
        $em = $this->getDoctrine()->getManager();
        $articleData = $em->getRepository(Article::class)->findOneById($id);


        return $this->render('default/article.html.twig', array(
            "articleData" => $articleData,
            "esAdmin" => $esAdm,
        ));
    }

    /**
     * @Route("/deleteArticle/{id}", name="deleteArticle")
     * @Method("GET")
     */
    public function deleteArticle($id)
    {
        $this->authHelper->adminIsLogedIn();
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository(Article::class)->findOneById($id);
        $em->remove($article);
        $flush = $em->flush();
        if ($flush == null) {
            return $this->redirectToRoute('index');
        } else {
            return $this->redirectToRoute('article', array(
                "id" => $id,
            ));
        }
    }

    /**
     * @Route("/logout", name="logout")
     * @Method("GET")
     */
    public function logout(Request $request)
    {
        $this->authHelper->logout();
        return $this->redirectToRoute('login');
    }
}

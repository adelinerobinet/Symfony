<?php
// src/Sdz/BlogBundle/Controller/BlogController.php
 
namespace Sdz\BlogBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sdz\BlogBundle\Entity\Article;
 
class BlogController extends Controller
{
  public function indexAction($page)
  {
    // On ne sait pas combien de pages il y a
    // Mais on sait qu'une page doit être supérieure ou égale à 1
    // Bien sûr pour le moment on ne se sert pas (encore !) de cette variable
    if ($page < 1) {
      // On déclenche une exception NotFoundHttpException 
      // Cela va afficher la page d'erreur 404
      // On pourra la personnaliser plus tard
      throw $this->createNotFoundException('Page inexistante (page = '.$page.')');
    }
 
    // Pour récupérer la liste de tous les articles : on utilise findAll()
    $articles = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('SdzBlogBundle:Article')
                     ->findAll();
 
    // L'appel de la vue ne change pas
    return $this->render('SdzBlogBundle:Blog:index.html.twig', array(
      'articles' => $articles
    ));
  }
 
  public function voirAction(Article $article)
  {
    // À ce stade, la variable $article contient une instance de la classe Article
    // Avec l'id correspondant à l'id contenu dans la route !
 
    // On récupère ensuite les articleCompetence pour l'article $article
    // On doit le faire à la main pour l'instant, car la relation est unidirectionnelle
    // C'est-à-dire que $article->getArticleCompetences() n'existe pas !
    $listeArticleCompetence = $this->getDoctrine()
                                   ->getManager()
                                   ->getRepository('SdzBlogBundle:ArticleCompetence')
                                   ->findByArticle($article->getId());
 
    return $this->render('SdzBlogBundle:Blog:voir.html.twig', array(
      'article'                 => $article,
      'listeArticleCompetence'  => $listeArticleCompetence
    ));
  }
 
  public function ajouterAction()
  {
    // La gestion d'un formulaire est particulière, mais l'idée est la suivante :
   
    if ($this->get('request')->getMethod() == 'POST') {
      // Ici, on s'occupera de la création et de la gestion du formulaire
   
      $this->get('session')->getFlashBag()->add('info', 'Article bien enregistré');
   
      // Puis on redirige vers la page de visualisation de cet article
      return $this->redirect( $this->generateUrl('sdzblog_voir', array('id' => 1)) );
    }
   
    // Si on n'est pas en POST, alors on affiche le formulaire
    return $this->render('SdzBlogBundle:Blog:ajouter.html.twig');
  }
 
  public function modifierAction($id)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()
               ->getEntityManager();
 
    // On récupère l'entité correspondant à l'id $id
    $article = $em->getRepository('SdzBlogBundle:Article')
                  ->find($id);
 
    // Si l'article n'existe pas, on affiche une erreur 404
    if ($article == null) {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant');
    }
 
    // Ici, on s'occupera de la création et de la gestion du formulaire
 
    return $this->render('SdzBlogBundle:Blog:modifier.html.twig', array(
      'article' => $article
    ));
  }
 
  public function supprimerAction($id)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()
               ->getEntityManager();
 
    // On récupère l'entité correspondant à l'id $id
    $article = $em->getRepository('SdzBlogBundle:Article')
                  ->find($id);
     
    // Si l'article n'existe pas, on affiche une erreur 404
    if ($article == null) {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant');
    }
 
    if ($this->get('request')->getMethod() == 'POST') {
      // Si la requête est en POST, on supprimera l'article
       
      $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');
 
      // Puis on redirige vers l'accueil
      return $this->redirect( $this->generateUrl('sdzblog_accueil') );
    }
 
    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
    return $this->render('SdzBlogBundle:Blog:supprimer.html.twig', array(
      'article' => $article
    ));
  }
 
  public function menuAction($nombre)
  {
    $liste = $this->getDoctrine()
                  ->getManager()
                  ->getRepository('SdzBlogBundle:Article')
                  ->findBy(
                    array(),          // Pas de critère
                    array('date' => 'desc'), // On trie par date décroissante
                    $nombre,         // On sélectionne $nombre articles
                    0                // À partir du premier
                  );
 
    return $this->render('SdzBlogBundle:Blog:menu.html.twig', array(
      'liste_articles' => $liste // C'est ici tout l'intérêt : le contrôleur passe les variables nécessaires au template !
    ));
  }
}
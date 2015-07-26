<?php

namespace Index\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Entity\otakus;

class DefaultController extends Controller
{
	public $fields;
    public function indexAction()
    {
    	$this->init();
    	return $this->render('IndexIndexBundle:Default:index.html.twig',array("fields" => $this->fields));
    }
    function init()
    {
        $this->fields = array();
    	$this->addfield($this->fields,"username","Username","text",array("minlength" => 5));
    	$this->addfield($this->fields,"email","Email","text", array("email" => true));
    	$this->addfield($this->fields,"password","Password","password",array("minlength" => 6));
    	$this->addfield($this->fields,"confirmpassword","Confirm Password","password",array("equalto" => "password","minlength" => 6));
    	//$this->addfield($this->fields,"aboutme","About Me","textarea");
    	//$this->addfield($this->fields,"hobbies","Hobbies","textarea");
    	//$this->addfield($this->fields,"favoriteAnimes","Favorite Animes","textarea");
    	//$this->addfield($this->fields,"favoriteGames","Favorite Games","textarea");
    	//$this->addfield($this->fields,"avatar","Avatar","file");    
    }
    public function addfield(&$fields,$name,$description,$type,$options = array())
    {
        $fields[$name] = new \stdClass();
    	$fields[$name]->name = $name;
    	$fields[$name]->description = $description;
    	$fields[$name]->type = $type;
        $fields[$name]->options = $options;

    }
    public function registerAction()
    {
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $em = $this->getDoctrine()->getManager();
        
        $repository = $em->getRepository('classesclassBundle:otakus');
        $searchedOtaku = $repository->findOneBy(array("email" => $request->request->get("email")));
        if ($searchedOtaku != null)
       	return new Response('<b style = "color:red">Email Address already exists</b>');
        $repository = $em->getRepository('classesclassBundle:otakus');
        $searchedOtaku = $repository->findOneBy(array("username" => $request->request->get("username"))); 
        if ($searchedOtaku != null)
       	return new Response('<b style = "color:red">Username already exists</b>');

        $arr =  $request->request->all();
        $newotaku = new otakus();
        $skipKeys = array("username","email","password");
        while (list($key, $value) = each($arr))
        {
            if (!in_array($key,$skipKeys))
        	$newotaku->$key = $value;
        }
        $newotaku->setUsername($request->request->get("username"));
        $newotaku->setEmail($request->request->get("email"));
        $newotaku->setPlainPassword($request->request->get("password"));
        $newotaku->addRole("User");
        $newotaku->nyanPoints = 5;
        $newotaku->setEnabled(1);

       	$files = $_FILES;
       	$path = "/var/www/web/avatars/";
       	$urlpath = "/avatars/";
        /*
       	foreach ($files as $key =>$uploadedFile) 
       	{
       		if ($uploadedFile['name'] != "")
       		{

	       		$uploadedFile['name'] = filter_var($uploadedFile['name'],FILTER_SANITIZE_EMAIL);
	       		while(file_exists($path.$uploadedFile['name']))
	       		$uploadedFile['name'] = rand(0,9).$uploadedFile['name'];

	       		if (move_uploaded_file($uploadedFile["tmp_name"],
				$path.$uploadedFile["name"]))
	       		$newotaku->$key = $urlpath.$uploadedFile['name'];
       		}
			
		}
        */



        $em->persist($newotaku);
        $em->flush();
        return new Response("");    	
    }
    public function generateAuthenticateSessionIdAction()
    {
        $request = $this->container->get('request');
        $session = $request->getSession();
        return new Response($session->get("_csrf/authenticate"));
    }
}
?>

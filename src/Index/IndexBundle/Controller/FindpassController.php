<?php

namespace Index\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class FindpassController extends Controller
{
    public function indexAction()
    {

	$request = Request::createFromGlobals();

	// the URI being requested (e.g. /about) minus any query parameters
	$request->getPathInfo();

	// retrieve GET and POST variables respectively
	$email = $request->request->get('email');

	$repository = $this->getDoctrine()->getRepository('classesclassBundle:members');
	//check if email address exists
	$client = $repository->findOneByEmail($email);;
		//if email exists, send email
		if (isset($client))
		{
		try
		{
		$name = $client->firstname.' '.$client->lastname;
		$password = $client->password;
		$id = $client->id;
		//pass in name, password, id and email address into email template
		$message = \Swift_Message::newInstance()
        ->setSubject('SmartPlan Enterprise Subscription')
        ->setFrom('info@smartplanenterprise.com')
        ->setTo($email)
        ->setBody(
        	$this->renderView(
                'IndexIndexBundle:Default:forgotpass.html.twig',
                array('email' => $email,'name' => $name,'password' => $password, 'id' => $id)
            				  )
       			  );
    	//render email template and send email
    	$this->get('mailer')->send($message);
		//send response to user that email has been sent
   		return new Response("email sent");
   		}
   		catch (Exception $e)
   		{
   		return new Response("Error occured!: ".$e->getMessage());
   		}

    	}
    	else  //else, inform user email does not exist
    	return new Response("email does not exist");

	}

}
?>

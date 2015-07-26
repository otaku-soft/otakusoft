<?php

namespace Index\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use classes\classBundle\Entity\defaultPlan;
use classes\classBundle\Entity\defaultModules;
use classes\classBundle\Entity\defaultLibraryInvestmentsLibrary;
use classes\classBundle\Entity\defaultLibraryHotTopics;
use classes\classBundle\Entity\defaultLibraryMcGrawHill;
use classes\classBundle\Entity\defaultMessaging;


class AuthenticateController extends Controller
{
	public $status;//records status of login
    public function loginAction()
    {
	    
	    //start session
	    $session = new Session();
		$session->start();
		//remove any previous admin sessions
		foreach ($this->sessionFields() as $field)
		if ($session->get($field) != null)
		$session->remove($field);

		$request = Request::createFromGlobals();
		// the URI being requested (e.g. /about) minus any query parameters
		$request->getPathInfo();
		$user =  $request->request->get('user', '');
		$pass =  $request->request->get('pass', '');
		$rememberme = $request->request->get('rememberme', '');

		$status = 0;

		$repository = $this->getDoctrine()->getRepository('classesclassBundle:advisers');
		//find if user name and password exists in members table
		$adviser = $repository->findOneBy(array('email' => $user, 'password' => $pass));

		$repository = $this->getDoctrine()->getRepository('classesclassBundle:members');
		if ($adviser != null)
		$member = $repository->findOneBy(array('id' => $adviser->userid));

		else
		$member = $repository->findOneBy(array('email' => $user, 'password' => $pass));
		//if user exists in table,
		if (isset($member))
			$status = 1;
			//if creds are valid
		if($status)
		{

			$session->set("userid",$member->id );

			if (!isset($adviser))
			{
				$this->createTableData("defaultPlan",$member->id);
				$this->createTableData("defaultModules",$member->id);
				$this->createTableData("defaultLibraryInvestmentsLibrary",$member->id);
				$this->createTableData("defaultLibraryHotTopics",$member->id);
				$this->createTableData("defaultLibraryMcGrawHill",$member->id);
				$this->createTableData("defaultMessaging",$member->id);
			}

			if (isset($adviser))
			{
				$session->set("adviserid",$adviser->id );

			}
			$session->save();
		}

		//return a response to ajax call
		$response =  new Response($status);

		if ($rememberme  && $status)
			{
			$expire=time()+60*60*24*30;
			$response->headers->setCookie(new Cookie('speEmail', $user,$expire));
			$response->headers->setCookie(new Cookie('spePass', $pass,$expire));
		}
		else
		{
			$response->headers->clearCookie('speEmail');
			$response->headers->clearCookie('spePass');
		}

		$this->status = $status;
		return $response;

	}
	public function loginWithRedirectAction()
	{
		$this->loginAction();
		if ($this->status == 1)
		return ($this->redirect($this->generateUrl('_portal_index')));
		else
		return new Response("failed to login");
	}

	private function createTableData($table,$userid)
	{
		$em = $this->getDoctrine()->getManager();

		$repository = $this->getDoctrine()->getRepository('classesclassBundle:'.$table);
		$currentdata = $repository->findBy(array("userid" => $userid));

		if (count($currentdata) > 0)//only copies data if no rows are returned
		return false;

		if ($table == "defaultPlan")
			$addeddata = new defaultPlan();
		if ($table == "defaultModules")
			$addeddata = new defaultModules();
		if ($table == "defaultLibraryInvestmentsLibrary")
			$addeddata = new defaultLibraryInvestmentsLibrary();
		if ($table == "defaultLibraryHotTopics")
			$addeddata = new defaultLibraryHotTopics();
		if ($table == "defaultLibraryMcGrawHill")
			$addeddata = new defaultLibraryMcGrawHill();
		if ($table == "defaultMessaging")
			$addeddata = new defaultMessaging();

			$addeddata->userid = $userid;
			$em->persist($addeddata);
			$em->flush();

		return true;
    }

	public function logoutAction()
	{
		$session = new Session();
		$session->start();

		foreach ($this->sessionFields() as $field)
		if ($session->get($field) != null)
		$session->remove($field);

		return $this->render('IndexIndexBundle:Default:logout.html.twig');
	}

	public function sessionFields()
	{
		return array("userid","planid","adviserid","editadviserid");
	}

}
?>

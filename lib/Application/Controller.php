<?php
/**
 *	@class Controller
 *		Base class for all controller objects
 *		A controller object is one which has one task, and one task only: to decide which models to load
 *		That's it. One of the biggest problems we run into with MVC is when we put too much application logic in the controllers, constraining ourselves to a specific implementation. Business logic is best left to the Model objects
 *
 * @author aj
 */
namespace Application;
class Controller extends Application {

	/*
	*	@var $params array
	*		array of arguments to be passed to the desired method
	*		usually this is retrieved from the $_POST scope
	*/
    var $params = array();

    /*
     *	@function preDispatch
     *		preprocesses request before dispatching to appropriate action
     *		right now it just halts progress by rendering the view, so nothing else can be rendered afterward
	 *		This is a bad idea, because the method in question will still be called; so I can post a DELETE request and the method will carry it out, even though the results will not be echoed to the browser
     */
    public function preDispatch(){
        //route to login screen if authentication is invalid
        if(!$this->Authenticate(array('post'=>$_POST,'cookie'=>$_COOKIE))->success){
            $this->getView()->load('login');
            $this->getView()->render();
			return false;
        }
		return true;
    }
}

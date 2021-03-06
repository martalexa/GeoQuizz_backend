<?php

namespace App\Controllers;

// use Ramsey\Uuid\Uuid;
// use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
* 
*/
use App\Controllers\Writer;
use App\Models\Partie;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class PartieController
 * @package App\Controllers
 */
class PartieController extends BaseController
{

    /**
     * @param $request
     * @param $response
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|static
     */
    public function getParties($request, $response, $args){
    	$parties = Partie::select()->get();
    	return Writer::json_output($response,200,$parties);
    }

    /**
     * @param $request
     * @param $response
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|static
     */
    public function getPartie($request, $response, $args) {
    	try {

    		$partie = Partie::select()->where("id","=",$args['id'])->firstOrFail();
    		$result = $partie;
    		$serie = $partie->serie()->first();
    		$result->serie = $serie;
    		$result->serie->city= $serie->city()->select()->first();


    		return Writer::json_output($response,200,$result);

    	} catch (ModelNotFoundException $exception){


			$notFoundHandler = $this->container->get('notFoundHandler');
			return $notFoundHandler($request,$response);
		}


    }

    /**
     * @param $request
     * @param $response
     * @return \Psr\Http\Message\ResponseInterface|static
     */
    public function createPartie($request, $response) {
    	$tab = $request->getParsedBody();
    	$partie = new Partie();
    	$partie->player_username = filter_var($tab["player_username"],FILTER_SANITIZE_STRING);
    	$partie->score = 0;
    	$partie->nb_photos=filter_var($tab["nb_photos"],FILTER_SANITIZE_NUMBER_FLOAT);
    	$partie->serie_id = filter_var($tab["serie_id"],FILTER_SANITIZE_NUMBER_FLOAT);
    	$partie->token = bin2hex(openssl_random_pseudo_bytes(32));
    	$partie->state =0;   

    	try{
    		$partie->save();
    		$result = $partie;
    		$serie = $partie->serie()->first();
    		$result->serie = $serie;
    		$result->serie->city= $serie->city()->select()->first();
    		$photos = $serie->photos()->select("id","description","url","lat","lng")->orderByRaw("RAND()")->take($partie->nb_photos)->get();
    		foreach($photos as $key => $photo){
    			$photo->url = $this->get('assets_path').'/uploads/' . $photo->url;
    			$photos[$key] = $photo;
    		}
    		$result->serie->photos = $photos;
            $result->serie->rules = [
                    "paliers" => $serie->paliers()->orderBy('coef', 'ASC')->get(),
                    "times" => $serie->times()->orderBy('nb_seconds', 'DESC')->get()
                ];
    		return Writer::json_output($response,201,$result);


    	} catch (\Exception $e){
    		$response = $response->withHeader('Content-Type','application/json')->withStatus(500);
    		return $response->getBody()->write(json_encode(['type' => 'error', 'error' => 500, 'message' => $e->getMessage()]));

    	}
    }

    /**
     * @param $request
     * @param $response
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|static
     */
    public function updateScore($request, $response, $args) {
    	$tab = $request->getParsedBody();
    	try {
    		$partie = Partie::where("token","=",$args["token"])->firstOrFail();
    	} catch (ModelNotFoundException $e) {
    		$notFoundHandler = $this->container->get('notFoundHandler');
    		return $notFoundHandler($request,$response);
    	}
    	try {
    		$partie->score = filter_var($tab["score"],FILTER_SANITIZE_NUMBER_FLOAT);
    		$partie->save();
    		return Writer::json_output($response,200,$partie);
    	} catch (Exception $e) {
    		$response = $response->withHeader('Content-Type','application/json')->withStatus(500);
    		return $response->getBody()->write(json_encode(['type' => 'error', 'error' => 500, 'message' => $e->getMessage()]));
    	}

    }

	//todo: changement de la partie : 0 - 1 - 2

    /**
     *
     */
    public function updateState($request, $response, $args){
    	$tab = $request->getParsedBody();
    	try {
    		$partie = Partie::where("token","=",$args["token"])->firstOrFail();
    		$partie->state =filter_var($tab["state"],FILTER_SANITIZE_NUMBER_FLOAT);
    		$partie->save();
    		return Writer::json_output($response,200,$partie);
    	} catch (ModelNotFoundException $e) {
    		$notFoundHandler = $this->container->get('notFoundHandler');
    		return $notFoundHandler($request,$response);
    	}
    }
}
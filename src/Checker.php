<?php
/**
 * Created by PhpStorm.
 * User: MosinVE
 * Date: 18.04.2017
 * Time: 11:15
 */

namespace instaCheck;
use GuzzleHttp\Client as HttpClient;
use Andreyco\Instagram\Client;
use GuzzleHttp\Subscriber\Instagram\ImplicitAuth;

/**
 * Class Checker
 * @package instaCheck
 */
class Checker
{
    /**
     * @var SessionStorage
     */
    private $_storage;
    /**
     * @var Client
     */
    private $_api;
    /**
     * @var HttpClient
     */
    private $_http;

    /**
     * Checker конструктор.
     * @param array $config
     * @param SessionStorage $storage
     */
    public function __construct(array $config, SessionStorage $storage)
    {
        $this->_api = new Client($config);
        $this->_storage = $storage;
        $this->_http = new HttpClient();
        $this->_http->setDefaultOption( 'verify', false);
	    $implicitAuth = new ImplicitAuth(
	    	array(
	    		'username' => $config['username'],
			    'password' => $config['password'],
			    'client_id'    => $config['apiKey'],
			    'redirect_uri' => $config['apiCallback'],
		    )
	    );
	    $this->_http->getEmitter()->attach($implicitAuth);
	    $this->_http->post('https://instagram.com/oauth/authorize');
	    $this->_storage->set('access_token', $implicitAuth->getAccessToken());
	    $this->makeSubscription();
	    $this->_api->setAccessToken($this->_storage->get('access_token'));
    }

        private function makeSubscription()
    {
    	$config = $this->_storage->get('config');
        $response = $this->_http->post('https://api.instagram.com/v1/subscriptions/', [
    		'future' => true,
		    'body' => [
			    'client_id' => $config['apiKey'],
			    'client_secret' => $config['apiSecret'],
			    'object' => 'user',
			    'aspect' => 'media',
			    'verify_token' => $this->_storage->get('access_token'),
			    'callback_url' => $config['apiCallback']
		    ]
	    ]);
	// Call the function when the response completes
	    $response->then(function ($response) {
		    echo $response->getStatusCode();
	    });
    }


	/**
     *
     * Статистика распределения подписчиков по категориям
	 * @param $followersCats
	 *
	 * @return array
	 */
	public function sortFollowers($followersCats)
    {
    	$followersCount = array('group1'=>0, 'group2'=>0, 'group3'=>0);
    	$followers = $this->_api->getUserFollower();
	    foreach ($followers->data as $follower){
		    $follows = $this->_api->getUser($follower->id)->data->counts->follows;
	    	if ($follows < $followersCats['treshold1']){
			    $followersCount['group1'] += 1;
		    }
		    if ($follows > $followersCats['treshold1'] and $follows < $followersCats['treshold2']){
			    $followersCount['group2'] += 1;
		    }
		    if ($follows > $followersCats['treshold2']){
			    $followersCount['group3'] += 1;
		    }
		    return json_encode([time()=> $followersCount]);
	    }
    }


    /**
     *
     * @param $mediaId
     */
    public function getFeedbackStats($mediaId)
    {
        $likes = $this->_api->getMediaLikes($mediaId)->data;
        $comments = $this->_api->getMediaComments($mediaId)->data;
        // TODO Обработка массивов с данными лайков и комментариев и вывод результата в формате JSON
    }

}
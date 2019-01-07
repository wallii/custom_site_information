<?php
/**
 * @file
 * @author Waliullah Khan
 * Contains \Drupal\custom_site_information\Controller\customRestController.
 * Please place this file under your custom_site_information(module_root_folder)/src/Controller/
 */
namespace Drupal\custom_site_information\Controller;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Access\AccessResult;
/**
 * Provides route responses for the Example module.
 */
class customRestController extends ControllerBase {
	/**
	* Entity query factory.
	*
	* @var \Drupal\Core\Entity\Query\QueryFactory
	*/
	protected $entityQuery;

	/**
	* Constructs a new CustomRestController object.

	* @param \Drupal\Core\Entity\Query\QueryFactory $entityQuery
	* The entity query factory.
	*/
	public function __construct(QueryFactory $entity_query) {
		$this->entityQuery = $entity_query;
	}
	/**
	* {@inheritdoc}
	*/
	public static function create(ContainerInterface $container) {
		return new static(
			$container->get('entity.query')
		);
	}
	/**
	* Checks access for this controller.
	*/
	public function access() {
		$config = \Drupal::config('custom_site_information.settings');
		$configVar = $config->get('formapi');
        $formAPI = \Drupal::routeMatch()->getParameter('apikey');
		$nodeID = (int)\Drupal::routeMatch()->getParameter('nodeid');
		$node = \Drupal\node\Entity\Node::load($nodeID);
		if($node) {
			$nodetype = $node->get('type')->getValue();
		
			if($nodetype[0]['target_id'] == 'page') {
				$nodeAccess = true;
			}
		} 
		
        if($formAPI == 'apikey' || $formAPI!= $configVar) {
			return AccessResult::forbidden();
		}
		
        if($formAPI == 'nodeid' || !isset($nodeAccess)) {
			return AccessResult::forbidden();
		} 
		return AccessResult::allowed();
	}
	/**
	* Return the 50 most recently updated nodes in a formatted JSON response.
	*
	* @return \Symfony\Component\HttpFoundation\JsonResponse
	* The formatted JSON response.
	*/
	
	public function getData() {
	    $nodeID = (int)\Drupal::routeMatch()->getParameter('nodeid');
		$node = \Drupal\node\Entity\Node::load($nodeID);
		
		$response_array = [];
		
	    $response_array['partnerName'] = 'Custom Module';
	    $response_array['partnerCode'] = '1';
	    $response_array['itemsCount'] = 1;
		$response_array['items'][] = [
			'title' => $node->get('title')->getValue()[0]['value'],
			'Body' => $node->get('body')->getValue()[0]['value'],
			];
		
       
		// Add the node_list cache tag so the endpoint results will update when nodes are updated.
		$cache_metadata = new CacheableMetadata();
		$cache_metadata->setCacheTags(['node_list']);
		// Create the JSON response object and add the cache metadata.
		$response = new CacheableJsonResponse($response_array);
		$response->addCacheableDependency($cache_metadata);
       
		return $response;
	}
   
  
}
?>
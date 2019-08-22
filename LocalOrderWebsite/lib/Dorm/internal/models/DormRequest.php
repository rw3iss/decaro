<?php
namespace Dorm\Models;

/**
 * /dorm/lib/core/dorm_request.php - Encapsulates details about the current request.
 *
 */

class DormRequest {
	public $uri = null; //the request path
	public $type = null; //GET, POST, PUT, DELETE, PATCH
	public $data = null; //arbitrary

	function __construct($uri = null, $type = null, $data = null) {
		$this->uri = $uri;
		$this->type = $type;
		$this->data = $data;
	}
}

abstract class RequestType {
	const GET = "GET";
	const POST = "POST";
	const PUT = "PUT";
	const DELETE = "DELETE";
}

?>
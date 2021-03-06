<?php

/**
 * Base class for SAML 2 bindings.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
abstract class SAML2_Binding {

	/**
	 * The destination of messages.
	 *
	 * This can be NULL, in which case the destination in the message is used.
	 */
	protected $destination;


	/**
	 * Retrieve a binding with the given URN.
	 *
	 * Will throw an exception if it is unable to locate the binding.
	 *
	 * @param string $urn  The URN of the binding.
	 * @return SAML2_Binding  The binding.
	 */
	public static function getBinding($urn) {
		assert('is_string($urn)');

		switch ($urn) {
		case SAML2_Const::BINDING_HTTP_POST:
			return new SAML2_HTTPPost();
		case SAML2_Const::BINDING_HTTP_REDIRECT:
			return new SAML2_HTTPRedirect();
		case SAML2_Const::BINDING_HTTP_ARTIFACT:
			return new SAML2_HTTPArtifact();
		default:
			throw new Exception('Unsupported binding: ' . var_export($urn, TRUE));
		}
	}


	/**
	 * Guess the current binding.
	 *
	 * This function guesses the current binding and creates an instance
	 * of SAML2_Binding matching that binding.
	 *
	 * An exception will be thrown if it is unable to guess the binding.
	 *
	 * @return SAML2_Binding  The binding.
	 */
	public static function getCurrentBinding() {
		switch ($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			if (array_key_exists('SAMLRequest', $_REQUEST) || array_key_exists('SAMLResponse', $_REQUEST)) {
				return new SAML2_HTTPRedirect();
			} elseif (array_key_exists('SAMLart', $_REQUEST) ){
				return new SAML2_HTTPArtifact();
			}
			break;

		case 'POST':
			if (isset($_SERVER['CONTENT_TYPE'])) {
				$contentType = $_SERVER['CONTENT_TYPE'];
				$contentType = explode(';', $contentType);
				$contentType = $contentType[0]; /* Remove charset. */
			} else {
				$contentType = NULL;
			}
			if (array_key_exists('SAMLRequest', $_REQUEST) || array_key_exists('SAMLResponse', $_REQUEST)) {
				return new SAML2_HTTPPost();
			} elseif ($contentType === 'text/xml') {
				return new SAML2_SOAP();
			}
			break;
		}

		throw new Exception('Unable to find the current binding.');
	}


	/**
	 * Retrieve the destination of a message.
	 *
	 * @return string|NULL $destination  The destination the message will be delivered to.
	 */
	public function getDestination() {

		return $this->destination;
	}


	/**
	 * Override the destination of a message.
	 *
	 * Set to NULL to use the destination set in the message.
	 *
	 * @param string|NULL $destination  The destination the message should be delivered to.
	 */
	public function setDestination($destination) {
		assert('is_string($destination) || is_null($destination)');

		$this->destination = $destination;
	}


	/**
	 * Send a SAML 2 message.
	 *
	 * This function will send a message using the specified binding.
	 * The message will be delivered to the destination set in the message.
	 *
	 * @param SAML2_Message $message  The message which should be sent.
	 */
	abstract public function send(SAML2_Message $message);


	/**
	 * Receive a SAML 2 message.
	 *
	 * This function will extract the message from the current request.
	 * An exception will be thrown if we are unable to process the message.
	 *
	 * @return SAML2_Message  The received message.
	 */
	abstract public function receive();

}

?>
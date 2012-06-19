<?php
/**
 * @deprecated In favor of Zend Framework class
 */
interface f_web_oauth_Signature
{
	
	/**
	 * @deprecated
	 */
	public function getName();
	
	/**
	 * @deprecated
	 */
	public function buildSignatureFromRequest($request);
}

/**
 * @deprecated
 */
class f_web_oauth_SignatureHmacSha1 implements f_web_oauth_Signature
{
	
	/**
	 * @deprecated
	 */
	public function buildSignatureFromRequest($request)
	{
		$token = $request->getToken();
		$consumer = $request->getConsumer();
		return base64_encode(hash_hmac("sha1", $request->getBaseSignature(), f_web_oauth_Util::encode($consumer->getSecret()) . '&' . f_web_oauth_Util::encode($token ? $token->getSecret() : ''), true));
	}
	
	/**
	 * @deprecated
	 */
	public function getName()
	{
		return "HMAC-SHA1";
	}
}

/**
 * @deprecated
 */
class f_web_oauth_SignatureRsaSha1 implements f_web_oauth_Signature
{
	
	/**
	 * @deprecated
	 */
	public function buildSignatureFromRequest($request)
	{
		throw new Exception("RSA-SHA1 not implemented!");
	}
	
	/**
	 * @deprecated
	 */
	public function getName()
	{
		return "RSA-SHA1";
	}
}

/**
 * @deprecated
 */
class f_web_oauth_SignaturePlaintext implements f_web_oauth_Signature
{
	
	/**
	 * @deprecated
	 */
	public function buildSignatureFromRequest($request)
	{
		return $request->getBaseSignature();
	}
	
	/**
	 * @deprecated
	 */
	public function getName()
	{
		return "PLAINTEXT";
	}
}
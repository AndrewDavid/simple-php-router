<?php
	namespace Pecee\Http\PostBody;
	
	class PostBody
	{
		public static function convertArrayToType(array $postBody, string $type)
		{
			$convertedBody = new $type;
			
			// CONVERT POST BODY ARRAY TO OBJECT OF TYPE $type
			foreach($postBody as $key => $value)
			{
				if(property_exists($convertedBody, $key))
					$convertedBody->{$key} = $value;
			}
			
			return self::castTypeProperties($convertedBody, $type);
		}
		
		public static function castTypeProperties($postBodyObject, $type)
		{
			$typeModel = new $type;
			
			// CAST POST BODY OBJECT PROPERTIES TO EXPECTED PROPERTY TYPES
			foreach(get_object_vars($postBodyObject) as $property => $value)
			{
				if(is_null($value))
					continue;
				
				if(property_exists($typeModel, $property) === false)
					continue;
				
				if(is_object($typeModel->$property))
				{
					if(is_array($value))
					{
						$value = self::convertArrayToType($value, gettype($typeModel->$property));
					}
					
					$value = self::castTypeProperties($value, gettype($typeModel->$property));
				}
				else
				{
					settype($value, gettype($typeModel->$property));
				}
				
				$typeModel->{$property} = $value;
			}
			
			return $typeModel;
		}
	}
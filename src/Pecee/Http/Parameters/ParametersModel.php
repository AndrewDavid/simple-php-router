<?php
	namespace Pecee\Http\Parameters;
	
	class ParametersModel
	{
		public static function convertArrayToType(array $parameters, string $type)
		{
			$convertedBody = new $type;
			
			// CONVERT PARAMETERS ARRAY TO OBJECT OF TYPE $type
			foreach($parameters as $key => $value)
			{
				if(property_exists($convertedBody, $key))
					$convertedBody->{$key} = $value;
			}
			
			return self::castTypeProperties($convertedBody, $type);
		}
		
		public static function castTypeProperties($parametersObject, string $type)
		{
			$typeModel = new $type;
			
			// CAST PARAMETERS OBJECT PROPERTIES TO EXPECTED PROPERTY TYPES
			foreach(get_object_vars($parametersObject) as $property => $value)
			{
				if(is_null($value))
					continue;
				
				if(property_exists($typeModel, $property) === false)
					continue;
				
				if(!isset($value) || is_null($value) || empty($value))
					continue;
				
				if(is_object($typeModel->{$property}))
				{
					$propertyType = get_class($typeModel->{$property});
					
					if(is_array($value))
					{
						$value = self::convertArrayToType($value, $propertyType);
					}
					else if(is_string($value))
					{
						$jsonValue = json_decode($value, true);
						if(json_last_error() == JSON_ERROR_NONE && is_array($jsonValue))
						{
							$value = self::convertArrayToType($jsonValue, $propertyType);
						}
					}
					
					$value = self::castTypeProperties($value, $propertyType);
				}
				else
				{
					$propertyType = gettype($typeModel->{$property});
					settype($value, $propertyType);
				}
				
				$typeModel->{$property} = $value;
			}
			
			return $typeModel;
		}
	}

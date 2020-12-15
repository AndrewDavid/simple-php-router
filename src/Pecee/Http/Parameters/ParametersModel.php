<?php
    namespace Pecee\Http\Parameters;

    class ParametersModel
    {
        public static function convertArrayToType(array $parameters, string $type)
        {
            $convertedBody = new $type;
            
            // CONVERT PARAMETERS ARRAY TO OBJECT OF TYPE $type
            foreach ($parameters as $key => $value) {
                if (property_exists($convertedBody, $key)) {
                    $convertedBody->{$key} = $value;
                }
                if (method_exists($convertedBody, '__set')) {
                    $convertedBody->{$key} = $value;
                }
            }
            
            return $convertedBody;
            //return self::castTypeProperties($convertedBody, $type);
        }
        
        public static function castTypeProperties($model, string $type)
        {
            if (is_array($model)) {
                $model = (object)$model;
            }
            /** @var Model $self */
            $self = $type::getFactory()::createMock();
    
            foreach (get_object_vars($model) as $index => $value) {
                if (is_null($value)) {
                    continue;
                }
    
                if (property_exists($self, $index) === false) {
                    if (method_exists($self, '__set') === false) {
                        continue;
                    }
                }
    
                $propertyType = gettype($self->{$index});
                if (is_object($self->{$index})) {
                    $propertyType = get_class($self->{$index});
                }
    
                if ($self->{$index} instanceof Model) {
                    if (is_string($value)) {
                        $jsonValue = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                        if (is_array($jsonValue) && json_last_error() === JSON_ERROR_NONE) {
                            $value = $jsonValue;
                        } else {
                            continue;
                        }
                    } elseif (is_array($value) === false) {
                        continue;
                    }
                    
                    $value = $self->{$index}::convert($value);
                    $value = $self->{$index}::cast($value);
                } elseif ($self->{$index} instanceof ValueObject) {
                    $value = is_array($value) ? $value['value'] : $value;
                    $value = $self->{$index}::fromNative($value);
                } elseif (is_object($self->{$index})) {
                    $value = new $propertyType($value);
                } else {
                    settype($value, $propertyType);
                }
    
                $self->{$index} = $value;
            }
    
            return $self;
        }
    }

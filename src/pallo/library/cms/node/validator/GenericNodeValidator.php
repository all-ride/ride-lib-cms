<?php

namespace pallo\library\cms\node\validator;

use pallo\library\cms\node\Node;
use pallo\library\cms\node\NodeModel;
use pallo\library\cms\node\NodeProperty;
use pallo\library\validation\exception\ValidationException;
use pallo\library\validation\ValidationError;
use pallo\library\String;

use \DateTime;

/**
 * Generic node validator
 */
class GenericNodeValidator implements NodeValidator {

    /**
     * Validates the node properties
     * @param pallo\library\cms\node\Node $node Node to be validated
     * @param pallo\library\cms\node\NodeModel $nodeModel Model of the nodes
     * @return null
     * @throws pallo\library\validation\exception\ValidationException when a
     * property is not valid
     */
    public function validateNode(Node $node, NodeModel $nodeModel) {
        $exception = new ValidationException();

        $this->validateRoute($node, $nodeModel, $exception);
        $this->validatePublicationDate($node, $exception);

        if ($exception->hasErrors()) {
            throw $exception;
        }
    }

    /**
     * Validates the route of the node
     * @param pallo\library\cms\node\Node $node Node to be validated
     * @param pallo\library\cms\node\NodeModel $nodeModel Model of the nodes
     * @param pallo\library\validation\exception\ValidationException $exception
     * @return null
     */
    protected function validateRoute(Node $node, NodeModel $nodeModel, ValidationException $exception) {
        if (!$node->getParent()) {
            return;
        }

        $nodeId = $node->getId();
        $rootNodeId = $node->getRootNodeId();

        $modelNodes = $nodeModel->getNodes();

        $propertyPrefix = Node::PROPERTY_ROUTE . '.';
        $lengthPropertyPrefix = strlen($propertyPrefix);

        $properties = $node->getProperties();
        foreach ($properties as $key => $property) {
            if (strpos($key, $propertyPrefix) !== 0) {
                continue;
            }

            $locale = substr($key, $lengthPropertyPrefix);

            $route = rtrim(ltrim($property->getValue(), '/'), '/');

            $tokens = explode('/', $route);
            foreach ($tokens as $index => $token) {
                if ($token) {
                    $token = new String($token);
                    $token = $token->safeString();
                }

                if (empty($token)) {
                    unset($tokens[$index]);
                } else {
                    $tokens[$index] = $token;
                }
            }

            $route = '/' . implode('/', $tokens);

            foreach ($modelNodes as $modelNode) {
                if ($modelNode->getId() == $nodeId || $modelNode->getRootNodeId() != $rootNodeId) {
                    continue;
                }

                $modelNodeProperties = $modelNode->getProperties();
                foreach ($modelNodeProperties as $propertyKey => $propertyValue) {
                    if (strpos($key, $propertyPrefix) !== 0) {
                        continue;
                    }

                    if ($propertyValue->getValue() != $route) {
                        continue;
                    }

                    $error = new ValidationError(
                        'error.route.used.node',
                        "Route '%route%' is already used by node %node%",
                        array(
                            'route' => $route,
                            'node' => $modelNode->getId()
                        )
                    );

                    $exception->addErrors(Node::PROPERTY_ROUTE, array($error));
                }
            }

            $property->setValue($route);
        }
    }

    /**
     * Validates the publication dates
     * @param pallo\library\cms\node\Node $node Node to be validated
     * @param pallo\library\validation\exception\ValidationException $exception
     * @return null
     */
    protected function validatePublicationDate(Node $node, ValidationException $exception) {
        $publishStart = $node->get(Node::PROPERTY_PUBLISH_START, null, false);
        $publishStop = $node->get(Node::PROPERTY_PUBLISH_STOP, null, false);

        $isPublishStartEmpty = empty($publishStart);
        $isPublishStopEmpty = empty($publishStop);

        if (!$isPublishStartEmpty) {
            $this->validateDate($publishStart, $exception, Node::PROPERTY_PUBLISH_START);
        }

        if (!$isPublishStopEmpty) {
            $this->validateDate($publishStop, $exception, Node::PROPERTY_PUBLISH_STOP);
        }

        if (!$isPublishStartEmpty && !$isPublishStopEmpty && $publishStart >= $publishStop) {
            $error = new ValidationError(
                'error.date.publish.negative',
                'Publish stop date cannot be before the publish start date'
            );

            $exception->addErrors(Node::PROPERTY_PUBLISH_STOP, array($error));
        }
    }

    /**
     * Validate a date configuration value
     * @param string $date date configuration value
     * @param pallo\library\validation\exception\ValidationException $exception
     * when a ValidationError occures, it will be added to this exception
     * @param string $fieldName name of the field to register possible errors
     * to the ValidationException
     * @return null
     */
    protected function validateDate($date, ValidationException $exception, $fieldName) {
        $dateTime = DateTime::createFromFormat(NodeProperty::DATE_FORMAT, $date);
        if ($dateTime) {
            return;
        }

        $error = new ValidationError(
            'error.value.invalid',
            '%value% is invalid',
            array(
                'value' => $date
            )
        );

        $exception->addErrors($fieldName, array($error));
    }

}
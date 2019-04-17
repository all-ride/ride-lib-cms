<?php

namespace ride\library\cms\node\validator;

use ride\library\cms\node\type\ReferenceNodeType;
use ride\library\cms\node\Node;
use ride\library\cms\node\NodeModel;
use ride\library\cms\node\NodeProperty;
use ride\library\validation\exception\ValidationException;
use ride\library\validation\ValidationError;
use ride\library\StringHelper;

use \DateTime;

/**
 * Generic node validator
 */
class GenericNodeValidator implements NodeValidator {

    /**
     * Array with the locale codes as key
     * @var array
     */
    protected $locales = array();

    /**
     * Sets the available locales of the system
     * @param array $locales Array with the locale codes as key
     * @return null
     */
    public function setLocales(array $locales) {
        $this->locales = $locales;
    }

    /**
     * Validates the node properties
     * @param \ride\library\cms\node\Node $node Node to be validated
     * @param \ride\library\cms\node\NodeModel $nodeModel Model of the nodes
     * @return null
     * @throws \ride\library\validation\exception\ValidationException when a
     * property is not valid
     */
    public function validateNode(Node $node, NodeModel $nodeModel) {
        $exception = new ValidationException();

        $this->validateRoute($node, $nodeModel, $exception);
        $this->validateHome($node, $nodeModel, $exception);
        $this->validatePublicationDate($node, $exception);

        if ($exception->hasErrors()) {
            throw $exception;
        }
    }

    /**
     * Validates the homepage state of the node
     * @param \ride\library\cms\node\Node $node Node to be validated
     * @param \ride\library\cms\node\NodeModel $nodeModel Model of the nodes
     * @param \ride\library\validation\exception\ValidationException $exception
     * @return null
     */
    protected function validateHome(Node $node, NodeModel $nodeModel, ValidationException $exception) {
        foreach ($this->locales as $locale => $null) {
            if (!$node->isHomepage($locale)) {
                continue;
            }

            $home = $nodeModel->getHomeNode($node->getRootNodeId(), $node->getRevision(), $locale);
            if (!$home || $home->getId() === $node->getId()) {
                continue;
            }

            $error = new ValidationError(
                'error.validation.home.exists',
                '%home% is already set as homepage',
                array(
                    'home' => $home->getId(),
                    'locale' => $locale,
                )
            );

            $exception->addErrors(Node::PROPERTY_ROUTE, array($error));

            break;
        }
    }

    /**
     * Validates the route of the node
     * @param \ride\library\cms\node\Node $node Node to be validated
     * @param \ride\library\cms\node\NodeModel $nodeModel Model of the nodes
     * @param \ride\library\validation\exception\ValidationException $exception
     * @return null
     */
    protected function validateRoute(Node $node, NodeModel $nodeModel, ValidationException $exception) {
        if (!$node->getParent()) {
            return;
        }

        $rootNode = $node->getRootNode();
        $rootNodeId = $rootNode->getId();
        $nodeId = $node->getId();

        $modelNodes = $nodeModel->getNodes($rootNodeId, $node->getRevision());

        $propertyPrefix = Node::PROPERTY_ROUTE . '.';
        $lengthPropertyPrefix = strlen($propertyPrefix);

        // loop all properties
        $properties = $node->getProperties();
        foreach ($properties as $key => $property) {
            if (strpos($key, $propertyPrefix) !== 0) {
                // we're only interested in route properties
                continue;
            }

            $routeLocale = substr($key, $lengthPropertyPrefix);
            $route = $property->getValue();

            // normalize route
            $route = trim($route, '/');
            $baseUrls[$routeLocale] = $rootNode->getBaseUrl($routeLocale);

            $tokens = explode('/', $route);
            foreach ($tokens as $index => $token) {
                if ($token) {
                    $token = StringHelper::safeString($token, '-', false);
                }

                if (empty($token)) {
                    unset($tokens[$index]);
                } else {
                    $tokens[$index] = $token;
                }
            }

            $route = '/' . implode('/', $tokens);

            // check for duplicate routes
            $errors = array();
            foreach ($modelNodes as $modelNode) {
                $modelNodeId = $modelNode->getId();
                if ($modelNodeId == $nodeId || $modelNode->getRootNodeId() != $rootNodeId || !$modelNode->hasParent() || $modelNode->getType() == ReferenceNodeType::NAME) {
                    // same node, different site or root node or a reference node

                    continue;
                }

                $modelNodeRoutes = $modelNode->getRoutes();
                foreach ($modelNodeRoutes as $locale => $modelNodeRoute) {
                    if (!array_key_exists($locale, $baseUrls)) {
                        $baseUrls[$locale] = $rootNode->getBaseUrl($locale);
                    }

                    if ($baseUrls[$routeLocale] . $route != $baseUrls[$locale] . $modelNodeRoute) {
                        continue;
                    }

                    $errors[$modelNodeId] = new ValidationError(
                        'error.route.used.node',
                        "Route '%route%' is already used by node %node% in locale %locale%",
                        array(
                            'route' => $route,
                            'node' => $modelNodeId,
                            'locale' => $locale,
                        )
                    );
                }
            }

            foreach ($errors as $error) {
                $exception->addErrors(Node::PROPERTY_ROUTE, array($error));
            }

            // update property with normalized route
            $property->setValue($route);
        }
    }

    /**
     * Validates the publication dates
     * @param \ride\library\cms\node\Node $node Node to be validated
     * @param \ride\library\validation\exception\ValidationException $exception
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
     * @param \ride\library\validation\exception\ValidationException $exception
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

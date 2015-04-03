<?php

// make this file accessible at http://localhost/index.php
// to execute tests for HttpImplementation

$uri = str_replace('/index.php/', '', $_SERVER['REQUEST_URI']);
$e = explode('?', $uri);

$path = $e[0];
$query = array();

if (isset($e[1])) {
    parse_str($e[1], $query);
}

$e = explode('/', $path);

$entity = $e[0];

if (isset($e[1])) {
    $id = (int) $e[1];
}

if (!$entity) {
    echo 'Success';
    return;
}

$mongo = new \MongoClient();
$database = $mongo->selectDB('httpimplementation');
$collection = $database->selectCollection($entity);

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        if (isset($id) && $id) {
            $object = $collection->findOne(array('_id' => $id));

            echo json_encode($object, JSON_PRETTY_PRINT);

            exit;
        }

        if (isset($query) && $query) {
            if (isset($query['_id'])) {
                $query['_id'] = (int) $query['_id'];
            }

            $objects = iterator_to_array($collection->find($query), false);
        } else {
            $objects = iterator_to_array($collection->find(), false);
        }

        echo json_encode($objects, JSON_PRETTY_PRINT);

        exit;

        break;

    case 'POST':
        $data = $_POST;

        $mostRecentDocument = $collection
            ->find(array(), array('_id' => 1))
            ->sort(array('_id' => -1))
            ->limit(1);
        $mostRecentDocument = iterator_to_array($mostRecentDocument, false);

        if ($mostRecentDocument) {
            $mostRecentDocument = $mostRecentDocument[0];

            $nextId = $mostRecentDocument['_id'] + 1;
        } else {
            $nextId = 1;
        }
        $data['_id'] = $nextId;

        $collection->insert($data);

        $data['_id'] = (int) $data['_id'];

        echo json_encode($data, JSON_PRETTY_PRINT);

        break;

    case 'PUT':

        parse_str(file_get_contents("php://input"), $data);
        unset($data['_id']);

        $object = array();
        if ($data) {
            $object = $collection->findAndModify(
                array('_id' => $id),
                array('$set' => $data),
                array(),
                array('new' => true)
            );
        }

        echo json_encode($object, JSON_PRETTY_PRINT);

        break;

    case 'DELETE':

        $data = $_POST;
        $collection->remove(array('_id' => $id));
}

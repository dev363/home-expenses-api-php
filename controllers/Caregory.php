<?php
include('db.php');

class Category extends DbConnection
{
    private $table = 'category';
    private $QueryName = 'Category';

    private function checkCategoryByNameD($name, $createdBy)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE deleted=0 AND createdBy = " . $createdBy . " AND name = '" . $name . "'";
        $result = mysqli_query($this->DB, $query);
        if ($result->num_rows === 0) {
            return false;
        }
        return true;
    }

    function getAllQuery()
    {
        $result = mysqli_query($this->DB, 'SELECT * FROM ' . $this->table . 'WHERE deleted=0');
        $response = [];
        if (!$result) {
            $response['status'] = 0;
            $response['status_message'] = $this->QueryName . " List not get.";
            $response['error'] = mysqli_error($this->DB);
        }
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        $response['status'] = 1;
        $response['status_message'] = $this->QueryName . " list get Successfully.";
        $response['data'] = $data;
        $this->ApiResponse($response);
    }

    function getQueryByTypeId($userId = null, $typeId = null)
    {
        $userID = $userId;
        $type = $typeId;

        if (!isset($_GET["userId"])) {
            $response['status_message'] = "userId is required";
            $this->ApiResponse($response);
        } else {
            $userID = $_GET['userId'];
        }
        if (!isset($_GET["type"])) {
            $response['status_message'] = "type is required";
            $this->ApiResponse($response);
        } else {
            $type = $_GET['type'];
        }

        $query = "SELECT * FROM " . $this->table . " WHERE deleted=0 AND createdBy = " . $userID . " AND type=" . $type;
        $result = mysqli_query($this->DB, $query);
        $response = [];
        if (!$result) {
            $response['status'] = 0;
            $response['status_message'] = $this->QueryName . " List not get.";
            $response['error'] = mysqli_error($this->DB);
            $response['query'] = $query;
        }
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        $response['status'] = 1;
        $response['status_message'] = $this->QueryName . " list get Successfully.";
        $response['data'] = $data;
        $this->ApiResponse($response);
    }

    function InsertQuery()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $response = [];
        $response['status'] = 0;


        if (!isset($input["name"])) {
            $response['status_message'] = "name is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["createdBy"])) {
            $response['status_message'] = "createdBy is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["type"])) {
            $response['status_message'] = "type is required";
            $this->ApiResponse($response);
        }

        $name = $input["name"];
        $createdBy = $input["createdBy"];
        $type = $input["type"];

        $isExist = $this->checkCategoryByNameD($name,  $createdBy);
        if ($isExist) {
            $response['status_message'] = "Already exist";
        } else {
            $query = "INSERT INTO " . $this->table . "(name, createdBy,type) VALUES('" . $name . "','" . $createdBy . "','" . $type . "')";
            if (mysqli_query($this->DB, $query)) {
                $response['status'] = 1;
                $response['status_message'] = $this->QueryName . " created Successfully.";
                $input['id'] = mysqli_insert_id($this->DB);
                $response['data'] = $input;
            } else {
                $response['status'] = 0;
                $response['status_message'] = $this->QueryName . " creation failed.";
                $response['error'] = mysqli_error($this->DB);
            }
        }
        $this->ApiResponse($response);
    }

    function UpdateQuery()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $response = [];
        $response['status'] = 0;


        if (!isset($input["id"])) {
            $response['status_message'] = "id is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["name"])) {
            $response['status_message'] = "name is required";
            $this->ApiResponse($response);
        }

        $name = $input["name"];
        $id = $input["id"];

        $query = "UPDATE " . $this->table . " SET `name`='" . $name . "' WHERE `id`='" . $id . "'";
        if (mysqli_query($this->DB, $query)) {
            $response['status'] = 1;
            $response['status_message'] = $this->QueryName . " updated Successfully.";
            $response['data'] = $input;
        } else {
            $response['status'] = 0;
            $response['status_message'] = $this->QueryName . " updation failed.";
            $response['error'] = mysqli_error($this->DB);
        }

        $this->ApiResponse($response);
    }

    function DeleteQuery($categoryId = null)
    {
        $id = $categoryId;

        if (!isset($_GET["id"])) {
            $response['status_message'] = "id is required";
            $this->ApiResponse($response);
        } else {
            $id = $_GET['id'];
        }

        $response = [];
        $query = "UPDATE " . $this->table . " SET `deleted`=1 WHERE `id`='" . $id . "'";
        if (mysqli_query($this->DB, $query)) {
            $response['status'] = 1;
            $response['status_message'] = $this->QueryName . " deleted Successfully.";
            $response['data'] = $_GET;
        } else {
            $response['status'] = 0;
            $response['status_message'] = $this->QueryName . " delete failed.";
            $response['error'] = mysqli_error($this->DB);
        }

        $this->ApiResponse($response);
    }


    private function ApiResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        die();
    }
}

// $api = new Category();
// $api->InsertQuery(array('name' => 'Testss463', 'createdBy' => 1));
// $api->UpdateQuery(array('name' => 'Test List', 'id' => 3));
// $api->DeleteQuery(5);
// $api->getAllQuery();
// $api->getQueryByUserId(1);

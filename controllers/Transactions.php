<?php
include('db.php');

class Transaction extends DbConnection
{
    private $table = 'transactions';
    private $QueryName = 'Transaction';

    private function checkUser($email)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = '" . $email . "'";
        $result = mysqli_query($this->DB, $query);
        if ($result->num_rows === 0) {
            return false;
        }
        return true;
    }

    function getAllQuery()
    {
        $result = mysqli_query($this->DB, 'SELECT * FROM ' . $this->table . ' WHERE deleted=0');
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

    function getDataWithFilters()
    {
        $page = 1;
        $perPage = 10;
        $sortColumn = null;
        $sortOrder = 'ASC';
        $conditions = [];
        $searchQuery = null;
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['page'])) {
            $page = $input['page'];
        }
        if (isset($input['perPage'])) {
            $perPage = $input['perPage'];
        }
        if (isset($input['sortColumn'])) {
            $sortColumn = $input['sortColumn'];
        }
        if (isset($input['sortOrder'])) {
            $sortOrder = $input['sortOrder'];
        }
        if (isset($input['conditions'])) {
            $conditions = $input['conditions'];
        }
        if (isset($input['searchQuery'])) {
            $searchQuery = $input['searchQuery'];
        }

        // Calculate the starting index for pagination
        $startIndex = ($page - 1) * $perPage;

        // Build the basic SELECT query
        $queryCount = 'SELECT COUNT(*) AS total_records FROM ' . $this->table;
        $querySelect = 'SELECT ' . $this->table . '.*, category.name AS categoryName FROM ' . $this->table . '
        LEFT JOIN category ON ' . $this->table . '.category = category.id';

        // Build main SELECT query with pagination and sorting
        $queryWhere = '';
        if (!empty($conditions) && $searchQuery) {
            $whereConditions = [];
            foreach ($conditions as $column => $value) {
                $whereConditions[] = "$column = '" . mysqli_real_escape_string($this->DB, $value) . "'";
            }
            $queryWhere .= ' WHERE ' . $this->table . '.deleted=0 AND ' . implode(' AND ', $whereConditions); // Where Condition Here

            $columns = ["category", "details", "pay"];
            $searchQuery = mysqli_real_escape_string($this->DB, $searchQuery);
            $searchConditions = [];
            foreach ($columns as $column) {
                $searchConditions[] = "LOWER(`$column`) LIKE '%" . mysqli_real_escape_string($this->DB, strtolower($searchQuery)) . "%'";
            }
            $queryWhere .= ' AND (' . implode(' OR ', $searchConditions) . ')'; // Search Condition Here
        } else if (!empty($conditions)) {
            $whereConditions = [];
            foreach ($conditions as $column => $value) {
                $whereConditions[] = "$column = '" . mysqli_real_escape_string($this->DB, $value) . "'";
            }
            $queryWhere .= ' WHERE' . $this->table . '.deleted=0 AND ' . implode(' AND ', $whereConditions); // Where Condition Here
        } else if ($searchQuery) {
            $columns = ["category", "details", "pay"];
            $searchQuery = mysqli_real_escape_string($this->DB, $searchQuery);
            $searchConditions = [];
            foreach ($columns as $column) {
                $searchConditions[] = "LOWER(`$column`) LIKE '%" . mysqli_real_escape_string($this->DB, strtolower($searchQuery)) . "%'";
            }
            $queryWhere .= ' WHERE ' . $this->table . '.deleted=0 AND (' . implode(' OR ', $searchConditions) . ')'; // Search Condition Here
        }

        // Execute query to get total records count
        $queryCount = $queryCount . $queryWhere;
        $result = mysqli_query($this->DB, $queryCount);
        $totalRecords = 0;
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $totalRecords = $row['total_records'];
        }


        $querySelect = $querySelect . $queryWhere;
        if ($sortColumn) {
            $querySelect .= ' ORDER BY ' . $sortColumn . ' ' . $sortOrder;
        }

        $querySelect .= ' LIMIT ' . $startIndex . ', ' . $perPage;

        $result = mysqli_query($this->DB, $querySelect);
        $response = [];

        if (!$result) {
            $response['status'] = 0;
            $response['status_message'] = $this->QueryName . " List not get.";
            $response['error'] = mysqli_error($this->DB);
        } else {
            $data = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            $response['status'] = 1;
            $response['status_message'] = $this->QueryName . " list get Successfully.";
            $response['data'] = $data;
            $response['total_records'] = $totalRecords;
            $response['total_pages'] = ceil($totalRecords / $perPage);
            $response['current_page'] = $page;
        }

        $this->ApiResponse($response);
    }


    function getQueryByUserId($id = null)
    {

        $response = [];
        $response['status'] = 0;

        if (!isset($_GET['userId'])) {
            $response['status_message'] = "userId is required";
            $this->ApiResponse($response);
        }

        $userID = isset($id) ? $id : $_GET['userId'];
        $query = "SELECT * FROM " . $this->table . " WHERE deleted=0 AND userid = " . $userID;
        $result = mysqli_query($this->DB, $query);

        if (!$result) {
            $response['status'] = 0;
            $response['status_message'] = $this->QueryName . " List not get.";
            $response['error'] = mysqli_error($this->DB);
            $response['query'] = $query;
            $this->ApiResponse($response);
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

    function getQueryByCategoryId($id = null)
    {

        $response = [];
        $response['status'] = 0;

        if (!isset($_GET['categoryId'])) {
            $response['status_message'] = "categoryId is required";
            $this->ApiResponse($response);
        }

        $categoryId = isset($id) ? $id : $_GET['categoryId'];
        $query = "SELECT * FROM " . $this->table . " WHERE deleted=0 AND category = " . $categoryId;
        $result = mysqli_query($this->DB, $query);

        if (!$result) {
            $response['status'] = 0;
            $response['status_message'] = $this->QueryName . " List not get.";
            $response['error'] = mysqli_error($this->DB);
            $response['query'] = $query;
            $this->ApiResponse($response);
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

        if (!isset($input["category"])) {
            $response['status_message'] = "category is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["details"])) {
            $response['status_message'] = "details is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["pay"])) {
            $response['status_message'] = "pay is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["userid"])) {
            $response['status_message'] = "userid is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["type"])) {
            $response['status_message'] = "type is required";
            $this->ApiResponse($response);
        }

        $category = $input["category"];
        $details = $input["details"];
        $pay = $input["pay"];
        $userid = $input["userid"];
        $type = $input["type"];

        $query = "INSERT INTO " . $this->table . " (type,category,details, pay,userid) VALUES('" . $type . "','" . $category . "','" . $details . "','" . $pay . "','" . $userid . "')";
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
        if (!isset($input["category"])) {
            $response['status_message'] = "category is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["details"])) {
            $response['status_message'] = "details is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["pay"])) {
            $response['status_message'] = "pay is required";
            $this->ApiResponse($response);
        }
        if (!isset($input["userid"])) {
            $response['status_message'] = "userid is required";
            $this->ApiResponse($response);
        }

        $category = $input["category"];
        $details = $input["details"];
        $pay = $input["pay"];
        $userid = $input["userid"];
        $id = $input["id"];

        $query = "UPDATE " . $this->table . " SET `category`='" . $category . "', `details`='" . $details . "', `pay`='" . $pay . "', `userid`='" . $userid . "' WHERE `id`='" . $id . "'";

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
        $query =  "UPDATE " . $this->table . " SET `deleted`=1 WHERE `id`='" . $id . "'";
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

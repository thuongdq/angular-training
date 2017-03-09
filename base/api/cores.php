<?php
/**
 * Framework tự tạo
 * Chỉ hỗ trợ Route, Filter và các chức năng hữu ích khác để tiện cho học viên học tập.
 * Author: Andy Vũ - Vũ Nguyễn Thiên Ân
 * Facebook: https://www.facebook.com/vunguyenthienan
 */
/**
 * Đăng ký một Custom Autoloader
 */
require('ActiveRecord.php');
spl_autoload_register(function ($filename) {
    if (file_exists("{$filename}.php")) {
        require("{$filename}.php");
    } elseif (file_exists("controllers/{$filename}.php")) {
        require("controllers/{$filename}.php");
    } elseif (file_exists("models/{$filename}.php")) {
        require("models/{$filename}.php");
    }
});

$cfg = ActiveRecord\Config::instance();
$cfg->set_model_directory('./models');
$cfg->set_connections(
    array(
        'development' => 'mysql://root@127.0.0.1/ask?charset=utf8',
    )
);

class App
{
    protected static $route; //Lưu trữ REQUEST_URI
    protected static $controller; //Lưu trữ tên Controller
    protected static $action; //Lưu trữ tên Action
    protected static $routeNotFound = false; //Flag sử dụng khi chuyển trang
    protected static $filters; //Bộ lọc
    protected static $salt = 'aX@8cs#M'; //Chuỗi mã hóa tự đặt
    public static $request;
    protected static $instance;

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Khởi chạy ứng dụng
     */
    public static function start()
    {
        static::getRoute();
        static::$request = Input::getMethodsForAngularJS();
    }

    /**
     * Lấy Routes
     * @return mixed
     */
    public static function getRoute()
    {
        static::$route = (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
        static::$route = static::removeURIParentFolders(static::$route);
        return static::$route;
    }

    /**
     * Chuyển đổi Array hoặc String thành JSON
     * @param array $data
     * @return string
     */
    public static function toJson($data = [], $callName = 'callback')
    {
        $data = json_encode($data);
        if (isset($_GET[$callName]) && !empty($_GET[$callName])) {
            return "{$_GET[$callName]}($data)";
        }
        return $data;
    }

    /**
     * Lấy và chuyển đổi JSON thành Array, nhưng nếu bạn thích trả về đối tượng. Hãy thay đối số $isArray thành false.
     * @param string $json
     * @param bool $isArray
     * @return mixed
     */
    public static function getJson($json = '', $isArray = true)
    {
        return json_decode($json, $isArray);
    }

    /**
     * Lấy các biến dựa trên Route được định nghĩa
     * @param $route
     * @param $uri
     * @return array
     */
    public static function getVar($route, $uri)
    {
        $route = preg_replace('/\//', '\/', $route);
        $uri = static::filterURI($uri);
        preg_match_all('/{(.*)}/U', $route, $vars);
        $allVars = [];
        if ($vars) {
            $route = preg_replace('/{.*}/U', '(.*)', $route);
            preg_match('/^' . $route . '$/U', $uri, $values);
            if ($values) {
                unset($values[0]);
                $values = array_values($values);
                foreach ($vars[1] as $key => $var) {
                    $allVars[$var] = isset($values[$key]) ? $values[$key] : '';
                }
            }
        }
        return $allVars;
    }

    /**
     * Xóa toàn bộ Querystring trên URI
     * @param $route
     * @return string
     */
    public static function filterURI($route)
    {
        $route = preg_replace('/\/?\?.*/u', '', $route);
        return trim($route, ' /');
    }

    /**
     * Xóa thư mục cha trên REQUEST_URI
     * @param $route
     * @return string
     */
    public static function removeURIParentFolders($route)
    {
        $parentFolders = trim(preg_replace('/\/[\w]+.php/U', '', $_SERVER['PHP_SELF']), ' /');
        $route = preg_replace('/' . preg_quote($parentFolders, '/') . '/u', '', $route);
        return trim($route, ' /');
    }

    /**
     * So sánh Route và URI
     * @param string $route
     * @param string $uri
     * @return bool
     */
    public static function compareRoute($route = '', $uri = '')
    {
        $route = preg_replace('/\//', '\/', $route); //Escapsing '/'
        preg_match_all('/({.*})/U', $route, $vars); //Get All Abstract Vars
        if ($vars) {
            foreach ($vars[1] as $var) {
//                $route = preg_replace('/' .preg_quote($var, '/'). '/U', '(.*)', $route);
                $route = preg_replace('/' . preg_quote($var, '/') . '/U', '[\w-]+', $route);
            }
        }
        if (preg_match('/^' . $route . '(\?.*|\/\?.*)?$/U', $uri, $uri)) {
            return true;
        }
        return false;
    }

    /**
     * Lấy Controller và Action
     * @param $callback
     * @return bool
     */
    public static function getControllerAndAction($callback)
    {
        $all = explode('@', $callback);
        if (strpos($all[0], 'Controller')) {
            static::$controller = $all[0];
            static::$action = $all[1];
            return true;
        }
        return false;
    }

    /**
     * Tạo Route - Hoạt động giống Laravel nhưng chỉ hỗ trợ Route và Filter đơn giản.
     * @param string $route
     * @param null $callback accepts Anonymous Function or Controller@Action
     */
    public static function route($route = '', $callback = null)
    {
        $route = trim($route, '/'); //Trim '/' on Route

        //Matching Route and run $callback when it is callable.
        //Dont misunderstand $route and static::$route. $route is defined by Developer and static::$route is REQUEST_URI from $_SERVER
        if (static::compareRoute($route, static::$route) && is_callable($callback)) {
            $vars = static::getVar($route, static::$route);
            echo call_user_func_array($callback, $vars);
            static::$routeNotFound = false;
            exit();
        } elseif (static::compareRoute($route, static::$route) && is_string($callback) && static::getControllerAndAction($callback)) {
            $vars = static::getVar($route, static::$route);
            echo call_user_func_array([static::$controller, static::$action], $vars);
            static::$routeNotFound = false;
            exit();
        } else {
            static::$routeNotFound = true;
        }
    }

    /**
     * Tạo Filter - Chạy code khi ứng dụng bắt đầu.
     * @param $routeApplied
     * @param $callback
     */
    public static function filter($routeApplied, $callback)
    {
        if (is_array($routeApplied) && is_callable($callback)) {
            foreach ($routeApplied as $route) {
                $route = preg_replace('/\//', '\/', $route);
                if (preg_match("/^$route$/U", static::$route)) {
                    if (!$callback()) {
                        exit();
                    }
                    break;
                }
            }
        } elseif (is_string($routeApplied) && is_callable($callback)) {
            $routeApplied = preg_replace('/\//', '\/', $routeApplied);
            if (preg_match("/^$routeApplied$/U", static::$route)) {
                if (!$callback()) {
                    exit();
                }
            }
        }
    }

    /**
     * Lấy chuỗi bất kỳ
     * @return string
     */
    public static function getHash()
    {
        $salt = static::$salt;
        return crypt(time(), '$1$' . static::$salt . '$');
    }

    /**
     * Mã hóa Mật khẩu
     * Bạn có thể thay đổi salt trong thuộc tính $salt
     * @param $password
     * @return string
     */
    public static function encryptPassword($password)
    {
        return crypt(md5($password), '$1$' . static::$salt . '$');
    }

    /**
     * Xác nhận Mật khẩu
     * Sử dụng phương thức này khi bạn dùng encryptPassword()
     * @param $password
     * @param $dbPassword
     * @return bool
     */
    public static function verifyPassword($password, $dbPassword)
    {
        return (static::encryptPassword($password) == $dbPassword) ? true : false;
    }

    /**
     * Kết thúc ứng dụng
     */
    public static function end()
    {
        $route = static::$route;
        if ($route && static::$routeNotFound) {
            try {
                throw new RouteException("Không tìm thấy Route!", 100, null, "Vui lòng kiểm tra lại Route \" <strong style='color: red'>$route</strong> \" và phương thức <strong style='color: red'>{$_SERVER['REQUEST_METHOD']}</strong> có được sử dụng không?");
            } catch (RouteException $e) {
                header('Content-Type: text/html');
                $e->getErrorPage();
            }
        }
    }
}

class Tool
{
    protected static $instance;

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Lấy hình ảnh thu nhỏ theo Name.
     * Format: 'yourImageName_thumb.ext'
     * @param string $filename tên File
     * @param string $suffix hậu tố
     * @return string
     */
    public static function getThumbnail($filename, $suffix = '_thumb')
    {
        $path = pathinfo($filename);
        return trim($path['dirname'] . '/' . $path['filename'] . $suffix . $path['extension'], '. \/');
    }

    /**
     * Cách tắt dành cho việc chuyển trang
     * @param $link
     */
    public static function redirect($link)
    {
        header('location: ' . $link);
        exit();
    }

    /**
     * Pagination cơ bản được viết dựa trên Bootstrap
     * @param int $totalPage
     * @param int $perPage
     * @param int $currentPage
     * @param int $range
     * @param $linkCallback
     * @return mixed
     */
    public static function pagination($totalPage = 0, $perPage = 10, $currentPage = 1, $range = 2, $linkCallback)
    {      
        $data['routerLinks'] = [];
        $data['render'] = '<nav><ul class="pagination">';
        if ($currentPage - 1 > 0) {
            $data['render'] .= sprintf('<li><a href="%s">&laquo;</a></li>', is_callable($linkCallback) ? $linkCallback($currentPage - 1) : '');
            $data['routerLinks'][] = [
                'label'=> '&laquo;',
                'link'=> is_callable($linkCallback) ? $linkCallback($currentPage - 1) : '',
                'isActive' => false
            ];
        }
        for ($i = $currentPage - $range; $i <= $totalPage; $i++) {
            if ($i == $currentPage && $i > 0) {
                $data['render'] .= sprintf('<li class="active"><a href="%s">%s</a></li>', is_callable($linkCallback) ? $linkCallback($i) : '', $i);
                $data['routerLinks'][] = [
                    'label'=> $i,
                    'link'=> is_callable($linkCallback) ? $linkCallback($i) : '',
                    'isActive' => true
                ];
            } elseif ($i > 0) {
                $data['render'] .= sprintf('<li><a href="%s">%s</a></li>', is_callable($linkCallback) ? $linkCallback($i) : '', $i);
                $data['routerLinks'][] = [
                    'label'=> $i,
                    'link'=> is_callable($linkCallback) ? $linkCallback($i) : '',
                    'isActive' => false
                ];
            }
            if ($i == $currentPage + $range) {
                break;
            }
        }
        if ($currentPage + 1 > 0 && $currentPage < $totalPage) {
            $data['render'] .= sprintf('<li><a href="%s">&raquo;</a></li>', is_callable($linkCallback) ? $linkCallback($currentPage + 1) : '');
            $data['routerLinks'][] = [
                'label'=> '&raquo;',
                'link'=> is_callable($linkCallback) ? $linkCallback($currentPage + 1) : '',
                'isActive' => false
            ];
        }
        $data['render'] .= '</ul></nav>';
        $data['currentPage'] = $currentPage;
        $data['totalPage'] = $totalPage;
        $data['perPage'] = $perPage;
        $data['range'] = $range;
        return $data;
    }

    /**
     * Chuyển đổi thời gian theo Timestamp cho MySQL
     * @param $time
     * @return bool|string
     */
    public static function convertToTimestamp($time)
    {
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * Chuyển đổi các kí tự của Việt Nam sang ASCII
     * @param $str
     * @return string
     */
    public static function VNConvert($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ặ|Ẳ|Ẵ)/", "A", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
        $str = preg_replace("/(Đ)/", "D", $str);
        $str = preg_replace("/(\,|\.|\"|\'|\?|\^|\@|\%|\(|\)|\[|\]|\<|\>|\:|\\\|\+|\*)/", "-", $str);
        $str = str_replace(" ", "-", str_replace("&*#39;", "", $str));
        $strip = "--";
        while (strpos($str, '--')) {
            $str = preg_replace("/--/", "-", $str);
        }
        $str = trim($str, '- ');
        return strtolower($str);
    }

}

class Input
{
    protected static $instance;

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Khởi tạo các phương thức cần thiết cho AngularJS
     */
    public static function getMethodsForAngularJS()
    {
        global $_PUT, $_PATCH, $_DELETE;

        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET) && !isset($_GET['_method'])) {
            return $_GET;
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST) && !isset($_POST['_method'])) {
            return $_POST;
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && !isset($_POST['_method'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            return $_POST = $data ? $data : $_POST;
        } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT' || isset($_POST['_method']) && $_POST['_method'] == 'PUT' && empty($_PUT)) {
            $data = json_decode(file_get_contents('php://input'), true);
            return $_PUT = $data ? $data : $_POST;
        } elseif ($_SERVER['REQUEST_METHOD'] == 'PATCH' || isset($_POST['_method']) && $_POST['_method'] == 'PATCH' && empty($_PATCH)) {
            $data = json_decode(file_get_contents('php://input'), true);
            return $_PATCH = $data ? $data : $_POST;
        } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE' || isset($_GET['_method']) && $_GET['_method'] == 'DELETE' && empty($_DELETE)) {
            return 1;
        }
        return false;
    }

    /**
     * Kiểm tra có phải phương thức GET không
     * @return bool
     */
    public static function isGet()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET') ? true : false;
    }

    /**
     * Cách tắt khi dùng isset() & !empty() trong Array/Object
     * @param string $input
     * @param $offset
     * @return bool
     */
    public static function isExists($input = '', $offset)
    {
        $flag = false;
        if (is_array($input)) {
            $flag = (isset($input[$offset]) && !empty($input[$offset])) ? true : false;
        } elseif (is_object($input)) {
            $flag = (isset($input->$offset) && !empty($input->$offset)) ? true : false;
        }
        return $flag;
    }

    /**
     * Lấy đối số tương ứng trong Request
     * @param string $key
     * @return bool
     */
    public static function get($key = null)
    {
        if (!is_null($key) && isset(App::$request[$key]) && !empty(App::$request[$key])) {
            return App::$request[$key];
        }
        if (is_null($key) && isset(App::$request) && !empty(App::$request)) {
            return App::$request;
        }
        return false;
    }
}

class Upload
{
    protected static $instance;
    protected static $mimes = ['image/jpeg', 'image/png', 'image/gif', 'application/zip', 'application/octet-stream', 'video/mp4'];
    protected static $maxSize = 2048;

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Khởi tạo các định dạng
     * @param $mimes
     * @return mixed
     */
    public static function setMimes($mimes)
    {
        static::$mimes = $mimes;
        return static::getInstance();
    }

    /**
     * Kiểm tra File có hợp lệ không?
     * @param $file
     * @return bool|string
     */
    public static function verify($file)
    {
        if ($file['error'] == 1) {
            return 'maxsize';
        } elseif (!in_array($file['type'], static::$mimes)) {
            return 'mimes';
        } elseif ($file['size'] > (static::$maxSize * 1024)) {
            return 'maxsize';
        }
        return true;
    }

    /**
     * Tạo thư mục
     * @param string $des
     * @return bool|string
     */
    public static function makeFolder($des)
    {
        $folderName = date('Y-m', time());
        if (!file_exists("$des/" . $folderName)) {
            mkdir("$des/" . $folderName, 0755);
        }
        return $folderName;
    }

    /**
     * Tạo tên File với thời gian
     * @param $fileName
     * @return string
     */
    public static function makeFileNameWithTime($fileName)
    {
        $fileInfo = pathinfo($fileName);
        return "{$fileInfo['filename']}_" . time() . ".{$fileInfo['extension']}";
    }

    /**
     * @param $from
     * @param $des
     * @return string
     */
    public static function moveWithDate($from, $des)
    {
        $fileName = pathinfo($from);
        $fileName = $fileName['basename'];
        $dir = "$des/" . static::makeFolder($des);
        rename($from, "$dir/$fileName");
        return trim("$dir/$fileName", ' ./');
    }

    /**
     * Đặt file vào hệ thống
     * @param $file
     * @param $des
     * @return string
     */
    public static function put($file, $des)
    {
        $des = trim($des, '/ ');
        move_uploaded_file($file['tmp_name'], "$des/" . static::makeFileNameWithTime($file['name']));
        return trim("$des/" . static::makeFileNameWithTime($file['name']), ' ./');
    }


}

/**
 * Đường dẫn tuyệt đối
 * @param string $url đường dẫn bạn muốn đến
 * @return string
 */
function baseUrl($url = '')
{
    return trim(BASEURL, '/ ') . '/' . trim($url, '/ ');
}

/**
 * Đường dẫn tuyệt đối
 * @param string $route đường dẫn bạn muốn đến
 * @return string
 */
function route($route = '')
{
    return baseUrl(trim($route, '/ '));
}

class Route
{
    protected static $instance;

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Viết Route cho phương thức GET
     * @param $route
     * @param null $callback
     */
    public static function get($route, $callback = null)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['_method'])) {
            App::route($route, $callback);
        }
    }

    /**
     * Viết Route cho phương thức POST
     * @param $route
     * @param null $callback
     */
    public static function post($route, $callback = null)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['_method'])) {
            App::route($route, $callback);
        }
    }

    /**
     * Viết Route cho phương thức PUT
     * @param $route
     * @param null $callback
     */
    public static function put($route, $callback = null)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'PUT' || ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_method']) && $_POST['_method'] == 'PUT')) {
            App::route($route, $callback);
        }
    }

    /**
     * Viết Route cho phương thức PATCH
     * @param $route
     * @param null $callback
     */
    public static function patch($route, $callback = null)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'PATCH' || ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_method']) && $_POST['_method'] == 'PATCH')) {
            App::route($route, $callback);
        }
    }

    /**
     * Viết Route cho phương thức DELETE
     * @param $route
     * @param null $callback
     */
    public static function delete($route, $callback = null)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE' || ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['_method']) && $_GET['_method'] == 'DELETE')) {
            App::route($route, $callback);
        }
    }

    /**
     * Viết Route cho phương thức bất kỳ
     * @param $route
     * @param null $callback
     */
    public static function any($route, $callback = null)
    {
        App::route($route, $callback);
    }
}

class Session
{
    protected static $instance;

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Lấy dữ liệu từ Session
     * @param String $session_key Key của Session
     * @return bool
     */
    public static function get($session_key = null)
    {
        if (!is_null($session_key) && isset($_SESSION[$session_key])) {
            return $_SESSION[$session_key];
        } elseif (is_null($session_key) && isset($_SESSION)) {
            return $_SESSION;
        }
        return false;
    }


    /***
     * Thêm dữ liệu mới vào Key trong Session, nên dữ liệu cũ nếu tồn tại sẽ được sử dụng lại.
     * @param String $session_key Key của Session
     * @param Mixed $data Data của Session
     * @return Session
     */
    public static function insert($session_key = null, $data = null)
    {
        $instance = self::getInstance();
        if (!is_null($session_key) && !isset($_SESSION[$session_key])) {
            $_SESSION[$session_key] = $data;
        } else {
            $old_session = $_SESSION[$session_key];
            $_SESSION[$session_key] = $data;
            $_SESSION[$session_key] = array_merge($_SESSION[$session_key], $old_session);
        }
        return $instance;
    }

    /**
     * Cập nhật dữ liệu mới vào Key trong Session, không giữ lại dữ liệu cũ
     * @param String $session_key Key của Session
     * @param Mixed $data Data của Session
     * @return Session
     */
    public static function update($session_key = null, $data = null)
    {
        $instance = self::getInstance();
        if (!is_null($session_key) && isset($_SESSION[$session_key])) {
            $_SESSION[$session_key] = $data;
        } elseif (!is_null($session_key)) {
            self::insert($session_key, $data);
        }
        return $instance;
    }

    /**
     * Xóa Session có Key chỉ định, nếu không có sẽ xóa hết
     * @param String $session_key Key của Session
     * @return Session
     */
    public static function destroy($session_key = null)
    {
        $instance = self::getInstance();
        if (is_null($session_key)) {
            session_destroy();
        } else {
            session_unset($_SESSION[$session_key]);
        }
        return $instance;
    }
}

/**
 * Bẫy lỗi được viết cho Route
 * Class RouteException
 */
class RouteException extends Exception
{
    private $customMess = '';
    private $mess = '';

    public function __construct($message, $code = 0, Exception $previous = null, $customMess = '')
    {
        $this->customMess = $customMess;
        $this->mess = $message;
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function detailMessage($message)
    {
        return '<h1>' . $message . '</h1><p>Code ' . $this->getCode() . ': ' . $this->customMess . '</p>';
    }

    public function getStyle()
    {
        return '<style type="text/css">a,h1{background-color:transparent;font-weight:400}#container,code{border:1px solid #D0D0D0}::selection{background-color:#E13300;color:#fff}::-moz-selection{background-color:#E13300;color:#fff}::-webkit-selection{background-color:#E13300;color:#fff}body{background-color:#fff;margin:40px;font:13px/20px normal Helvetica,Arial,sans-serif;color:#4F5155}a{color:#039}h1{color:#444;border-bottom:1px solid #D0D0D0;font-size:19px;margin:0 0 14px;padding:14px 15px 10px}code{font-family:Consolas,Monaco,Courier New,Courier,monospace;font-size:12px;background-color:#f9f9f9;color:#002166;display:block;margin:14px 0;padding:12px 10px}#container{margin:10px;box-shadow:0 0 8px #D0D0D0}p{margin:12px 15px}</style>';
    }

    public function getErrorPage()
    {
        http_response_code(400);
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>' . $this->mess . '</title></head>';
        echo $this->getStyle();
        echo '<div id="container">';
        echo $this->detailMessage($this->mess);
        echo '<p>Line ' . $this->getLine() . ': ' . $this->getFile() . '</p>';
        echo '</div>';
        echo '</html>';
    }
}

class Response {
    private static $instance;
    private static $data;

    public static function getInstance() {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        if (static::$data === null) {
            static::$data['data'] = [];
            static::$data['errors'] = [];
            static::$data['messages'] = [];
        }
        return static::$instance;
    }

    public static function printData() {        
        echo App::toJson(static::$data);
    }

    public static function insertData($key = '', $data = []) {
        $instance = static::getInstance();
        if (is_string($key) && $key && isset(static::$data['data'][$key])) {
            static::$data['data'][$key][] = $data;
        } elseif (is_array($key) && $key) {
            static::$data['data'][] = $key;
        }
        return $instance;
    }

    public static function updateData($key = '', $data = []) {
        $instance = static::getInstance();
        if (is_string($key) && $key) {
            static::$data['data'][$key] = $data;
        } elseif (is_array($key) && $key) {
            static::$data['data'] = $key;
        }
        return $instance;
    }

    public static function mergeData($data) {
        $instance = static::getInstance();
        if (is_array($data)) {
            static::$data['data'] = array_merge(static::$data['data'], $data);
        } else {
            static::insert($data);
        }
        return $instance;
    }

    public static function removeKey($key) {
        $instance = static::getInstance();
        if (isset(static::$data[$key])) {
            unset(static::$data[$key]);
        }
        return $instance;
    }

    public static function warning($error) {
        $instance = static::getInstance();
        static::$data['errors'][] = $error;
        return $instance;
    }

    public static function messages($message) {
        $instance = static::getInstance();
        static::$data['messages'][] = $message;
        return $instance;
    }

    public static function get() {
        static::getInstance();
        return static::$data;
    }

    public static function update($key = '', $data = []) {
        $instance = static::getInstance();
        if (is_string($key) && $key) {
            static::$data[$key] = $data;
        } else {
            static::$data = $key;
        }
        return $instance;
    }

    public static function isValid() {
        if (count(static::$data['errors']) > 0) {
            return false;
        }
        return true;
    }
}
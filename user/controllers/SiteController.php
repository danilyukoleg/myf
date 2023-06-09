<?php

namespace user\controllers;

use common\models\BudgetLog;
use common\models\CdbArticle;
use common\models\CdbCategory;
use common\models\CdbSubcategory;
use common\models\Clients;
use common\models\CreditPay;
use common\models\disk\Cloud;
use common\models\helpers\Robokassa;
use common\models\helpers\TelegramBot;
use common\models\User;
use common\models\UserModel;
use common\models\UsersBills;
use common\models\UsersBonuses;
use common\models\UsersCertificates;
use common\models\UsersProperties;
use core\models\ResendVerificationEmailForm;
use core\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Expression;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use core\models\PasswordResetRequestForm;
use core\models\ResetPasswordForm;
use core\models\SignupForm;
use core\models\ContactForm;
use yii\web\Response;
use yii\data\Pagination;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup', 'index', 'login', 'external-login'],
                'rules' => [
                    [
                        'actions' => ['logout', 'signup', 'login', 'external-login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index', 'external-login'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        $actions = [
            'external-login'
        ];
        if (in_array($action->id, $actions))
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionExternalLogin()
    {
        $g = $_GET;
        do {
            if (!Yii::$app->user->isGuest) break;
            if (empty($g['url']) || empty($g['auth']))
                break;
            $user = User::findOne(['auth_key' => $g['auth']]);
            if (empty($user))
                break;
            $user->status = User::STATUS_ACTIVE;
            if ($user->update() !== false)
                $login = Yii::$app->user->login(User::findIdentity($user->id), 3600 * 24 * 30);
            if (!$login)
                break;
            return Yii::$app->response->redirect($g['url']);
        } while (false);
        return $this->goHome();
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
//            'error' => [
//                'class' => 'yii\web\ErrorAction',
//            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }


    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionUserLogin()
    {
        if (!empty($_COOKIE['is_valid_user'])) {
            $vals = explode("::", $_COOKIE['is_valid_user']);
            $id = (int)$vals[0];
            $hash = $vals[1];
            $newHash = md5("{$id}::" . User::SPECIAL_LOGIN_HASH);
            if ($hash === $newHash) {
                $user = User::findOne($id);
                if (!empty($user))
                    Yii::$app->user->login($user);
            }
        }
        return $this->goHome();
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
            return $this->redirect('https://myforce.ru/site/logout');
        } else {
            return $this->redirect('https://myforce.ru');
        }
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionProceed()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!empty($_POST['data'])) {
            $user = UserModel::findOne(Yii::$app->getUser()->getId());
            if (!empty($user)) {
                $user->is_client = (int)$_POST['data'];
                $user->update();
                return ['status' => 'success', 'url' => $user->is_client === -1 ? '/lead-force/provider' : '/lead-force/client'];
            }
        }
        return ['status' => 'error', 'message' => 'Ошибка выбора'];
    }

    public function actionBalance()
    {
        $user = Yii::$app->getUser();
        $realUser = User::findOne(['id' => $user->id]);
        #Логи бюджета
        $dates = new \DateTime();
        $dates->modify('last day of this month');
        $lastDay = $last_day_this_month = $dates->format('Y-m-d 23:59:59');
        $firstDay = date("Y-m-01 00:00:00");


        /* фильтр истории */
        $filters = ['AND'];
        if (!empty($_GET['filters'])) {
            $dateFind = $_GET['filters'];
            if (!empty($dateFind['first'])) {
                $filters[] = ['>=', 'date', date('Y-m-d 00:00:00', strtotime($dateFind['first']))];
            }
            if (!empty($dateFind['last'])) {
                $filters[] = ['<=', 'date', date('Y-m-d 23:59:59', strtotime($dateFind['last']))];
            }
        } else {
            $filters[] = ['>=', 'date', $firstDay];
            $filters[] = ['<=', 'date', $lastDay];
        }
        /* фильтр истории */

        $budget = BudgetLog::find()->where(['user_id' => $user->id])->andWhere($filters)->asArray();
        $countQuery = clone $budget;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $pages->setPageSize(10);
        $pages->pageParam = 'balance-page';
        $models = $budget->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('date asc')
            ->all();
        #Логи бюджета

        #СЧЕТА
        /* фильтр счетов */
        $filt = ['AND'];
        if (!empty($_GET['filt'])) {
            $dateFin = $_GET['filt'];
            if (!empty($dateFin['first'])) {
                $filt[] = ['>=', 'date', date('Y-m-d 00:00:00', strtotime($dateFin['first']))];
            }
            if (!empty($dateFin['last'])) {
                $filt[] = ['<=', 'date', date('Y-m-d 23:59:59', strtotime($dateFin['last']))];
            }
        } else {
            $filt[] = ['>=', 'date', $firstDay];
            $filt[] = ['<=', 'date', $lastDay];
        }
        /* фильтр счетов */
        $bills = UsersBills::find()->where(['user_id' => $user->id])->andWhere($filt)->asArray();
        $countQueryBills = clone $bills;
        $pagesBills = new Pagination(['totalCount' => $countQueryBills->count()]);
        $pagesBills->setPageSize(10);
        $pagesBills->pageParam = 'bill-page';
        $modelsBills = $bills->offset($pagesBills->offset)
            ->limit($pagesBills->limit)
            ->orderBy('date desc')
            ->all();
        #СЧЕТА

        #АКТЫ
        /* фильтр актов */
        $filtAct = ['AND'];
        if (!empty($_GET['filtAct'])) {
            $dateAct = $_GET['filtAct'];
            if (!empty($dateAct['first'])) {
                $filtAct[] = ['>=', 'date', date('Y-m-d 00:00:00', strtotime($dateAct['first']))];
            }
            if (!empty($dateAct['last'])) {
                $filtAct[] = ['<=', 'date', date('Y-m-d 23:59:59', strtotime($dateAct['last']))];
            }
        } else {
            $filtAct[] = ['>=', 'date', $firstDay];
            $filtAct[] = ['<=', 'date', $lastDay];
        }
        /* фильтр актов */
        $acts = UsersCertificates::find()->where(['user_id' => $user->id])->andWhere($filtAct)
            ->asArray();
        $countQueryActs = clone $acts;
        $pagesActs = new Pagination(['totalCount' => $countQueryActs->count()]);
        $pagesActs->setPageSize(10);
        $pagesActs->pageParam = 'act-page';
        $modelsActs = $acts->offset($pagesActs->offset)
            ->limit($pagesActs->limit)
            ->orderBy('date desc')
            ->all();
        #АКТЫ

        #статистика
        $expression = "(date >= '{$firstDay}') AND (date <= '{$lastDay}') AND (user_id={$user->id}) AND id IN (SELECT MAX(id) FROM budget_log GROUP BY DATE(date))";
        $stats = BudgetLog::find()
            ->where(new Expression($expression))
            ->asArray()
            ->orderBy('date asc')
            ->all();
        #статистика

        $client = Clients::find()->where(['user_id' => $user->id])->asArray()->one();
        return $this->render('balance',
            [
                'user' => $user,
                'balance' => $models,
                'pages' => $pages,
                'pagesBills' => $pagesBills,
                'pagesActs' => $pagesActs,
                'real_user' => $realUser,
                'client' => $client,
                'bills' => $modelsBills,
                'acts' => $modelsActs,
                'stats' => $stats
            ]);
    }

    public function actionCreateBalanceLink()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!empty($_POST['value']) && !empty($_POST['hash'])) {
            if ($_POST['value'] > 1000000)
                return ['status' => 'error', 'message' => 'Сумма платежа не может быть больше 1 млн. рублей.'];
            $user = Yii::$app->getUser();
            $client = Clients::findOne(['user_id' => $user->id]);
            if (!empty($client->company_info) && !empty($client->requisites)) {
                $reqs = json_decode($client->requisites, 1);
                if ($reqs !== null && isset($reqs['fiz'])) {
                    $required = ['f', 'i', 'address', 'phone', 'email'];
                    foreach ($required as $key => $item) {
                        if (empty($reqs['fiz'][$item])) {
                            return ['status' => 'error', 'message' => 'Реквизиты плательщика заполнены некорректно'];
                        }
                    }
                    $newhash = md5(Robokassa::PASSWORD_MAIN_1 . "::{$user->id}");
                    if ($newhash === $_POST['hash']) {
                        $keys = [
                            'description' => 'Пополнение баланса личного кабинета',
                            'price' => $_POST['value'],
                            'shp' => ['Shp_description' => "Пополнение баланса личного кабинета", 'Shp_user' => $user->id, 'Shp_redirect' => "https://user.myforce.ru/lead-force/provider/balance"]
                        ];
                        $robokassa = new Robokassa($keys);
                        $robokassa->create__pay__link();
                        return ['status' => 'success', 'url' => urldecode($robokassa->url)];
                    } else
                        $rsp = ['status' => 'error', 'message' => 'Ошибка контрольной суммы'];
                } else
                    $rsp = ['status' => 'error', 'message' => 'Необходимо заполнить реквизиты физ.лица-плательщика'];
            } else
                $rsp = ['status' => 'error', 'message' => 'Необходимо заполнить <a href="' . Url::to(['prof']) . '">данные профиля</a> для совершения платежей'];
        } else
            $rsp = ['status' => 'error', 'message' => 'Не указаны обязательные параметры'];
        return $rsp;
    }

    public function actionCreateBill()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!empty($_POST['value']) && !empty($_POST['hash'])) {
            if ($_POST['value'] > 1000000)
                return ['status' => 'error', 'message' => 'Сумма платежа не может быть больше 1 млн. рублей.'];
            $user = Yii::$app->getUser();
            $client = Clients::findOne(['user_id' => $user->id]);
            $restriction = UsersBills::find()->where(['AND', ['>=', 'date', date("Y-m-d 00:00:00")], ['<=', 'date', date("Y-m-d 23:59:59")], ['user_id' => $user->id]])->count();
            if ($restriction >= 2)
                return ['status' => 'error', 'message' => 'Выставление более 2 счетов в день запрещено. <br> Попробуйте, пожалуйста, завтра'];
            if (!empty($client->company_info) && !empty($client->requisites)) {
                $newhash = md5(Robokassa::PASSWORD_MAIN_1 . "::{$user->id}");
                if ($newhash === $_POST['hash']) {
                    $reqs = json_decode($client->requisites, 1);
                    if ($reqs !== null && isset($reqs['jur'])) {
                        $required = ['inn', 'ogrn', 'kpp', 'bank', 'bik', 'rs', 'ks', 'organization', 'director', 'jur_address', 'real_address'];
                        foreach ($required as $key => $item) {
                            if (empty($reqs['jur'][$item])) {
                                return ['status' => 'error', 'message' => 'Реквизиты плательщика заполнены некорректно'];
                            }
                        }
                        $cloud = new Cloud($user->id);
                        $file = $cloud->create__bill($reqs['jur'], $_POST['value']);
                        $bills = new UsersBills();
                        $bills->name = "Пополнение баланса личного кабинета";
                        $bills->user_id = $user->id;
                        $bills->act_data = json_encode($file['responseData'], JSON_UNESCAPED_UNICODE);
                        $bills->value = $_POST['value'];
                        $bills->link = $file['download'];
                        if (!in_array('error', $file)) {
                            if (file_exists($file['real']) && $bills->save()) {
                                $rsp = ['status' => 'success', '__object' => $bills->id];
                            } else
                                $rsp = ['status' => 'error', 'message' => 'Ошибка сохранения счета'];
                        } else
                            $rsp = ['status' => 'error', 'message' => 'Ошибка сети. Повторите попытку позже'];
                    } else
                        $rsp = ['status' => 'error', 'message' => 'Реквизиты плательщика заполнены некорректно'];
                } else
                    $rsp = ['status' => 'error', 'message' => 'Ошибка контрольной суммы'];
            } else
                $rsp = ['status' => 'error', 'message' => 'Необходимо заполнить <a href="' . Url::to(['prof']) . '">данные профиля</a> для совершения платежей'];
        } else
            $rsp = ['status' => 'error', 'message' => 'Не указаны обязательные параметры'];
        return $rsp;
    }

    public function actionProf()
    {
        $user = Yii::$app->user;
        $model = User::find()->where(['id' => $user->getId()])->asArray()->one();
        $client = Clients::find()->where(['user_id' => $user->getId()])->asArray()->one();
        $propertis = UsersProperties::find()->where(['user_id' => $user->getId()])->asArray()->one();
        return $this->render('prof', ['user' => $user, 'model' => $model, 'client' => $client, 'propertis' => $propertis]);
    }

    public function actionProfileSaver()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!empty($_POST['fields']) && is_array($_POST['fields'])) {
            $fields = $_POST['fields'];
            $errors = [];
            $user_id = Yii::$app->getUser()->getId();
            $client = Clients::findOne(['user_id' => $user_id]);
            if (empty($client)) {
                $client = new Clients();
                $client->user_id = $user_id;
            }
            $client->f = $fields['familiya'];
            $client->i = $fields['imya'];
            $client->o = $fields['otchestvo'];
            $client->email = $fields['email'];
            if (empty($client->email)) {
                $errors[] = 'Необходимо указать почту!';
            }
            if ($fields['type'] === 'fiz') {
                if (!empty($fields['fiz']['address_registration'])) {
                    $client->company_info = json_encode(['fiz' => ['address' => $fields['fiz']['address_registration']]], JSON_UNESCAPED_UNICODE);
                } else {
                    $errors[] = 'Необходимо указать адрес регистрации по паспорту';
                }
            } else {
                $keys = ['address_jur', 'address_real', 'companyName', 'director', 'web_site', 'work',];
                if (!empty($fields['jur']) && is_array($fields['jur'])) {
                    $companyInfo = [];
                    foreach ($keys as $item) {
                        if ($item == 'web_site' || $item == 'work') {
                            $companyInfo['jur'][$item] = $fields['jur'][$item];
                            continue;
                        }
                        if (empty($fields['jur'][$item])) {
                            $errors[] = 'Не указаны обязательные поля юридического лица';
                            break;
                        } else {
                            $companyInfo['jur'][$item] = $fields['jur'][$item];
                        }
                    }
                    $client->company_info = json_encode($companyInfo, JSON_UNESCAPED_UNICODE);
                }
            }
            if ($client->validate() && empty($errors)) {
                if ((empty($client->id) && $client->save()) || $client->update() !== false) {
                    return ['status' => 'success'];
                } else {
                    return ['status' => 'error', 'message' => 'Ошибка сохранения данных'];
                }
            } else {
                return ['status' => 'error', 'validation' => [$errors, $client->errors]];
            }
        } else return ['status' => 'error', 'message' => 'Данные не указаны'];
    }

    public function actionKnowledge()
    {
        $category = CdbCategory::find()->asArray()->all();
        $subcategory = CdbSubcategory::find()->asArray()->all();
        $popularArticle = CdbArticle::find()->orderBy('views desc')->asArray()->limit(2)->all();

        return $this->render('knowledge', [
            'category' => $category,
            'subcategory' => $subcategory,
            'popularArticle' => $popularArticle,
        ]);
    }

    public function actionKnowledgecat($link = null)
    {
        $category = CdbCategory::find()->where(['link' => $link])->asArray()->one();
        if (empty($category)) {
            return $this->redirect('knowledge');
        }
        $subcategory = CdbSubcategory::find()->where(['category_id' => $category['id']])->asArray()->all();
        $popularArticle = CdbArticle::find()->orderBy('views desc')->asArray()->limit(2)->all();
        return $this->render('knowledgecat', [
            'category' => $category,
            'subcategory' => $subcategory,
            'popularArticle' => $popularArticle,
        ]);
    }

    public function actionKnowledgearticle($link = null)
    {
        $subcategory = CdbSubcategory::find()->where(['link' => $link])->asArray()->one();
        if (empty($subcategory)) {
            return $this->redirect('knowledge');
        }
        $category = CdbCategory::find()
            ->where(['id' => $subcategory['category_id']])
            ->asArray()
            ->one();
        $article = CdbArticle::find()
            ->where(['category_id' => $category['id']])
            ->andWhere(['subcategory_id' => $subcategory['id']])
            ->asArray()
            ->all();
        $popularArticle = CdbArticle::find()->orderBy('views desc')->asArray()->limit(2)->all();
        return $this->render('knowledgearticle', [
            'category' => $category,
            'subcategory' => $subcategory,
            'article' => $article,
            'popularArticle' => $popularArticle,
        ]);
    }

    public function actionKnowledgepage($link = null)
    {
        $article = CdbArticle::findOne(['link' => $link]);
        if (empty($article)) {
            return $this->redirect('knowledge');
        }
        $id = $article->id;
        $category = CdbCategory::find()->where(['id' => $article->category_id])->asArray()->one();
        $subcategory = CdbSubcategory::find()->where(['id' => $article->subcategory_id])->asArray()->one();
        $popularArticle = CdbArticle::find()->orderBy('views desc')->where(['!=', 'id', $id])->asArray()->limit(2)->all();
        $moreArticle = CdbArticle::find()->where(['!=', 'id', $id])->asArray()->limit(2)->all();

        if (!empty($_COOKIE['Views'])) {
            $cookie = $_COOKIE['Views'];
            $array = json_decode($cookie, true);
            if (!in_array($id, $array)) {
                $array[] = $id;
                $cookie = json_encode($array, JSON_UNESCAPED_UNICODE);
                setcookie('Views', $cookie, time() + 3600 * 24 * 365 * 10, '/');
                $article->views = $article->views + 1;
                $article->update();
            }
        } else {
            $cookLink = json_encode([$id], JSON_UNESCAPED_UNICODE);
            setcookie('Views', $cookLink, time() + 3600 * 24 * 365 * 10, '/');
            $article->views = $article->views + 1;
            $article->update();
        }


        return $this->render('knowledgepage', [
            'article' => $article,
            'category' => $category,
            'subcategory' => $subcategory,
            'popularArticle' => $popularArticle,
            'moreArticle' => $moreArticle,
        ]);
    }

    public function actionArticleSearch()
    {
        $filter = ['AND'];
        if (!empty($_GET['word'])) {
            $filter = ['OR',
                ['like', 'title', '%' . $_GET['word'] . '%', false],
                ['like', 'description', '%' . $_GET['word'] . '%', false],
                ['like', 'text', '%' . $_GET['word'] . '%', false],
                ['like', 'tags', '%' . $_GET['word'] . '%', false],
            ];
        }
        $article = CdbArticle::find()->where($filter)->asArray();
        $pages = new Pagination(['totalCount' => $article->count(), 'pageSize' => 10]);
        $posts = $article->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        $popularArticle = CdbArticle::find()->orderBy('views desc')->asArray()->limit(2)->all();
        return $this->render('article-search', [
            'article' => $posts,
            'popularArticle' => $popularArticle,
            'pages' => $pages,
        ]);
    }

    public function actionTestss()
    {
//        function translit($s) {
//            $s = (string) $s; // преобразуем в строковое значение
//            $s = trim($s); // убираем пробелы в начале и конце строки
//            $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
//            $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>'',' '=>'-'));
//            return $s; // возвращаем результат
//        }
//
//        $article = CdbArticle::find()->where(['like', 'text', "%/img/file%", false])->all();
//        foreach ($article as $item){
//            $arrEngName = [];
//            $re = '/\/img\/file\/(\S+)(?=\")/mi';
//            $matches = [];
//            preg_match_all($re, $item->text, $matches, PREG_SET_ORDER, 0);
//            foreach ($matches as $i) {
//                foreach ($i as $v)
//                    if (!stristr($v, '/img/file/')) {
//                        $arrEngName[$v] = translit(urldecode($v));
//                    }
//            }
//            foreach ($arrEngName as $k => $i){
//                $rusName = urldecode($k);
//                if (file_exists("../web/img/file/{$rusName}")){
//                    rename("../web/img/file/{$rusName}", "../web/img/file/{$i}"); //Переименование файла
//                }
//                $item->text = str_replace($k, $i, $item->text);
//                $item->update();
//            }
//        }
    }

    public function actionCreditPaymentSend()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!empty($_POST['price'])) {
            $price = $_POST['price'];
            $user_id = Yii::$app->getUser()->getId();
            if (empty($user_id)){
                return $this->redirect('https://myforce.ru/site/fail-credit-pay');
            }
            $payHistory = CreditPay::find()->where(['user_id'=>$user_id])->orderBy('id desc')->one();
            if (date('Y-m-d H:i:s') < date('Y-m-d H:i:s', strtotime('+1 day', strtotime($payHistory->date)))){
                return $this->redirect('https://myforce.ru/site/fail-credit-pay');
            }
            $model = new CreditPay();
            $model->user_id = $user_id;
            $model->price = $price;
            if ($model->save()){
                $hash = md5("{$price}::{$user_id}::{$model->id}::dfg4ttvsd");
                $data = [
                    "tradeID" => "610105507000002",
                    "creditType" => "1",
                    "goods" => [
                        [
                            "category" => "RGB_GOODS_CATEGORY_130",
                            "name" => "Пополнение личного кабинета MYFORCE",
                            "price" => $price,
                            "quantity" => "1"
                        ]
                    ],
                    "successURL" => "https://myforce.ru/site/confirm-credit-pay?uid={$user_id}&price={$price}&hash={$hash}&pay_id={$model->id}",
                    "failURL" => "https://myforce.ru/site/fail-credit-pay"
                ];
                $link = 'https://ecom.otpbank.ru/smart-form?' . http_build_query($data);
                return $this->redirect($link);
            } else {
                return $this->redirect('https://myforce.ru/site/fail-credit-pay');
            }
        } else {
            return $this->redirect('https://myforce.ru/site/fail-credit-pay');
        }
    }

    public function actionWheelRolling() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (empty($_POST['roll']))
            return ['success' => false, 'message' => 'Ошибочный запрос'];
        $user = User::findOne(Yii::$app->getUser()->getId());
        if (empty($user))
            return ['success' => false, 'message' => 'Пользователь не найден'];
        $bonus = UsersBonuses::findOne(['user_id' => $user->id]);
        if (empty($bonus) || $bonus->bonus_points === 0) {
            if ($user->budget >= 1000) {
                $user->budget -= 1000;
                $user->update();
                return ['success' => true, 'bonus' => $bonus->bonus_points, 'balance' => $user->budget];
            } else
                return ['success' => false, 'message' => 'Баланс менее 1000 рублей'];
        } elseif ($bonus->bonus_points < 100) {
            $bp = $bonus->bonus_points;
            $bonus->bonus_points = 0;
            if (10 * (100 - $bp) > $user->budget)
                return ['success' => false, 'message' => 'Недостаточно средств для пополнения бонусов. Необходимо пополнить баланс'];
            else {
                $bonus->update();
                $user->budget -= 10 * (100-$bp);
                $user->update();
                return ['success' => true, 'bonus' => $bonus->bonus_points, 'balance' => $user->budget];
            }
        } else {
            $bonus->bonus_points -= 100;
            $bonus->update();
            return ['success' => true, 'bonus' => $bonus->bonus_points, 'balance' => $user->budget];
        }
    }

    public function actionPrize() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!empty($_POST['prize'])) {
            $user = User::findOne(Yii::$app->getUser()->getId());
            $tg = new TelegramBot();
            $tg->new__message($tg::prize__message($_POST['prize'], $user->id), $tg::PEER_SALE);
            return ['success' => true];
        } else
            return ['success' => false, 'message' => 'Приз не определен'];
    }

    public function actionFortune()
    {
        return $this->render('fortune');
    }

}

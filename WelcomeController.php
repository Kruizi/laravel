<?php namespace Laravel\Http\Controllers;
use Illuminate\Routing\Controller;
use Laravel\People;
class WelcomeController extends Controller {

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /**
         * Берем URL(Из бд или еще откуда-нибудь)
         * Отправляем ее в функцию
         * Функция возращает ссылки которые она нашла
         * Далее проверяем на существование ссылки
         * Через for делаем цикл на проверку доступности
         * И добавляем в БД, потом через админку мы увидим какие ссылки доступны, какие нет
         * Если что потом можем изменить
         */
        $url = 'http://php.net/';
        $isDomainAvailible = $this->isDomainAvailible($url);
        if (!empty($isDomainAvailible)) {
            for($i = 0; $i < count($isDomainAvailible['1']); $i++)
            {
                $good = get_headers($url.$isDomainAvailible['1'][$i], 1);
                $people = People::insertGetId(
                    array('name' => ''.$good['0'].'','desc' => ''.$isDomainAvailible['1'][$i].'')
                );
            }
        }else
        {
            $good = 'Error array';
        }
        return view('index', compact('good'));
    }

    /**
     * @return array
     */
    private function isDomainAvailible($url)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL,$url);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); //агент которым мы представимся
        curl_setopt ($ch, CURLOPT_TIMEOUT, 15 ); // таймаут
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec ($ch);
        curl_close($ch);
        // Находим и сохраняем нужный фрагмент
        preg_match_all('/<(?:[aA][\s]+[hH][rR][eE][fF]|[iI][mM][gG][\s]+.*[\s]+[sS][rR][cC])[\s]*=[\s]*[\'"]([^\s">]+)[\'"]/',$result,$links);
        return $links; //возвращаем  полученную страницу

    }
}

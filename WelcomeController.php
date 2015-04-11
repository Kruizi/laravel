<?php namespace Laravel\Http\Controllers;
use Illuminate\Routing\Controller;
use Laravel\People;
class WelcomeController extends Controller {

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** Ссылка на сайт(Она может быть любая, потом просто переправить метод откуда ее брать) */
        $url = 'http://php.net/';
        /** Отправляем ссылку в метод который вернет страницу */
        $isDomainAvailible = $this->isDomainAvailible($url);
        /** Проверяем на пустоту */
        if (!empty($isDomainAvailible)) {
            /** Делаем цикл, где указываем сколько элементов в массиве и указываем ему [1] то есть на готовые ссылки
             * бзе html
             */
            for($i = 0; $i < count($isDomainAvailible['1']); $i++)
            {
                /** Если попадутся ссылки подобия "http://www.php.net" или "https://"*/
                if(strripos($isDomainAvailible['1'][$i], 'http') == 'true')
                {
                    /** Добавляем в бд уже обрезаную строку с помощью функции substr and strlen */
                    People::insertGetId(
                        array('desc' => ''.substr($isDomainAvailible['1'][$i], strlen($url)).'')
                    );
                }else
                {
                    /** Ну если нету то добавляем просто ссылку */
                    People::insertGetId(
                        array('desc' => ''.$isDomainAvailible['1'][$i].'')
                    );
                }
                /**/
            }
            /** Выводит ссылки */
            $good = People::get();
        }else
        {
            /** Если пришел пустой массив выведем ошибкуу */
            $good = 'Error array';
        }
        return view('index', compact('good'));
    }

    /**
     * @return \Illuminate\View\View
     * Проверяем ссылки на доступность
     */
    public function checkUrl()
    {
        /** Берем количество ссылок в базе */
        $countUrl = People::where('admin', '=', 0)->count();
        /** Сделаем цикл для обновлении ссылок */
        for($i = 1; $i < $countUrl; $i++)
        {
            /** Берем саму ссылку */
            $users = People::where('id', '=', $i)->addSelect('desc')->get();
            foreach($users as $u)
            {
                /** Проверяем на статус */
                $goods = get_headers('http://php.net/'.$u->desc);
                /** Обновляем */
                People::where('id', '=', $i)->update(['name' => $goods[0]]);
            }

        }
        return view('check', compact('goods'));
    }

    /**
     * @return array
     */
    private function isDomainAvailible($url)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL,$url);// ссылка
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

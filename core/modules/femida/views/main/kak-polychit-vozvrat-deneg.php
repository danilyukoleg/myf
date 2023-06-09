<?php
/* @var $this yii\web\View */

use yii\helpers\Url;

$guest = Yii::$app->user->isGuest;

$this->title = 'Как получить возврат денег за франшизу: что делать, если она не удалась';
?>

<div class="container">
  <div class="pow articles">
    <div class="rol-3 NavsMenu">
      <div class="NavsNews">
        <h6>Новости франчайзинга</h6>
        <div class="NavsNews__inner">
          <?php foreach ($news as $key => $item) : ?>
            <div class="newsNav">
              <a target="_blank" class="linkTelega" href="<?= Url::to(['/news-page/' . $item->link]) ?>">
                <p><?= $item->title ?></p>
              </a>
              <hr>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <aside class="NavsStat d-None">
        <h6>Популярные статьи</h6>
        <div class="NavsStat__inner">
          <a href="<?= Url::to(['kak-covid-povliyal-na-rasklad-sil']) ?>" class="popularАrticlesCard">
            <img src="<?= Url::to(['/img/Rectangle 46.webp']) ?>" alt="picture of article">
            <p class="cardTextP1">Как COVID-19 повлиял на расклад сил на страховом рынке</p>
            <p class="cardTextP2">Статья по франчайзингу</p>
          </a>
          <a href="<?= Url::to('vidu-franchizy') ?>" class="popularАrticlesCard">
            <img src="<?= Url::to(['/img/Rectangle 46-1.webp']) ?>" alt="picture of article">
            <p class="cardTextP1">Виды франшиз: классификация по характеру взаимоотношений</p>
            <p class="cardTextP2">Статья по франчайзингу</p>
          </a>
        </div>
        <div style="background-image: url('<?= Url::to(['/img/foneing.webp']) ?>')" class="yourCabinet">
          <p class="yourCabinetp1">Ваш личный кабинет MYFORCE</p>
          <p class="yourCabinetp2">Управление сделками. Системный контроль качества заявок и работы
            менеджеров</p>
          <div style="padding: 13px 0">
            <?php if (!$guest) : ?>
              <a href="https://user.myforce.ru/" class="orangeLink">
                <span>Кабинет</span>
                <img src="<?= Url::to(['/img/whiteShape.svg']) ?>" alt="arrow">
              </a>
            <?php else : ?>
              <a class="orangeLink BLS6CBORID-BTN">
                <span>Регистрация</span>
                <img src="<?= Url::to(['/img/whiteShape.svg']) ?>" alt="arrow">
              </a>
            <?php endif; ?>
          </div>
        </div>
      </aside>
    </div>
    <div class="rol-9 BodyTexted">
      <div style="background-image: url('<?= Url::to(['/img/Rectangle 178-5.webp']) ?>')" class="photoArticles">
        <h1>Как получить возврат денег за франшизу: что делать, если она не удалась</h1>
        <h2>Расскажем о том, какие существуют причины, этапы и другие способы расторжения договора</h2>
        <p class="photoArticlesp2">Прокопенко Ольга, 24.12.2020</p>
      </div>
      <div class="textArticles">
        <br>
        <p>Франчайзинг: для кого-то это веселое начало бизнеса, для кого-то-новый образ жизни, но во всех
          случаях это бизнес, способ заработать деньги. Его суть заключается не только в тщательном соблюдении
          формальной стороны, тщательном выполнении условий, но и в практическом результате. Шаблон любого
          успешного бизнеса, перенесенный на другие условия: город, люди, ситуация, может распространиться под
          давлением обстоятельств. Что делать, если модная франшиза не приходит в ваш город? Можно ли вернуть
          деньги за франшизу, если прошло время и обещанный успех не пришел?</p>
        <h3>Причины расторжения договора с франчайзером</h3>
        <p>Франчайзинговые отношения — это партнерство. У обеих сторон есть свои интересы. Нет ничего плохого
          в том, что франчайзер решает, как продать франшизу, и красиво завершает свои сделки. Нет ничего
          плохого в том, что франчайзи, недовольный процессом и результатом, ищет варианты вернуть деньги
          за франшизу, которые к нему не пришли.</p>
        <div class="pow bluTextCardBlock">
          <div class="rol-6 bluTextCard">
            <p class="bluTextCardP1">Со стороны франчайзи</p>
            <p>• Недоволен реальной маркетинговой, рекламной, обещанной поддержкой;</p>
            <p>• Обучение было неполным, халатным, поверхностным;</p>
            <p>• Указанный состав франчайзингового пакета (виды поддержки, помощь с оборудованием,
              IT-технологии, мотивация) оказался незначительным, условным или неработающим;</p>
            <p>• Франшиза оказалась завуалированным «сетевым маркетингом»;</p>
            <p>• Фактическая продажа оказалась плачевной, заявленные данные по доходности и обороту даже
              близко не приближаются к реальности;</p>
            <p>• Партнер авторитарно диктует каждый шаг в бизнес-процессах, независимо от реалий другого
              города или региона.</p>
          </div>
          <div class="rol-6 bluTextCard">
            <p class="bluTextCardP1">Со стороны франчайзера</p>
            <p>• Настаивает на том, что партнер не отвечает требованиям, рекомендациям и не соблюдает нормы,
              прописанные в договоре;</p>
            <p>• Франчайзи не прошел обучение в полном объеме, не следовал указаниям, проигнорировал этапы
              или форматы обучения;</p>
            <p>• Франчайзи не соблюдает условия договора, отвлекается от брендбука, делает неучтенные
              инновации;</p>
            <p>• Франчайзи не проявляет инициативы и ожидает поддержки от партнера, указанной
              в договоре;</p>
            <p>• Требования франчайзи завышены, а результаты крайне низкие. Проверки регулярно выявляют
              случаи халатности и нарушений;</p>
            <p>• Партнер не организован, требует независимости и не выполняет условия договора.</p>
          </div>
        </div>
        <h3>Поэтапное расторжение договора франчайзинга</h3>
        <p>Если претензии непреодолимы, бизнес не состоялся, то самое время расторгнуть договор франшизы. Этот
          процесс включает в себя следующие шаги:<br><br>
          <span class="blackText">Шаг 1.</span> Начните с грамотного анализа договора: обсудите с юристом,
          проверьте все пункты, выясните, что в нем главное-что является предметом сделки.<br><br>
          <span class="blackText">Шаг 2.</span> Договоритесь со своим партнером обо всех условиях расторжения
          договора, убедитесь, что они выполнимы;<br><br>
          <span class="blackText">Шаг 3.</span> Причины и порядок расторжения договора должны быть прописаны
          в договоре-следуйте ему. Не забывайте про пункт «условия договора»;<br><br>
          <span class="blackText">Шаг 4.</span> Если срок не установлен, расторжение договора возможно
          в одностороннем порядке с обязательным уведомлением за шесть месяцев;<br><br>
          <span class="blackText">Шаг 5.</span> Если срок определен, то расторжение договора невозможно раньше
          установленного срока (помните об этом);<br><br>
          <span class="blackText">Шаг 6.</span> Если договором предусмотрены платежи, то необходимо сообщить
          о прекращении правоотношения за 30 дней с обязательным условием наличия требуемой суммы;<br><br>
          <span class="blackText">Шаг 7.</span> Односторонний отказ без участия суда возможен только в том
          случае, если партнер грубо нарушил условия договора или имеет просроченные платежи. Если нарушение
          устранено, то причина нивелируется;<br><br>
          <span class="blackText">Шаг 8.</span> Для любых действий до и без суда необходима серьезная причина
          с представленными доказательствами;<br><br>
          <span class="blackText">Шаг 9.</span> В апелляционном суде с предписанными требованиями
          и доказательствами.
        </p>
        <h4>Если вы проинформировали партнера и он согласен начать процедуру расторжения договора, вам
          необходимо сделать следующее:</h4>
        <p>1. Необходимо составить документ с адвокатом и списком лиц, участвующих в процессе;<br><br>
          2. Ввод сведений о концессионном договоре с датой составления нового договора;<br><br>
          3. Договориться о дате расторжения договора;<br><br>
          4. Укажите информацию о выполнении Сторонами всех договорных условий (уплаченные роялти, возврат
          оборудования и т. д);<br><br>
          5. Укажите реквизиты сторон и подписи участников;<br><br>
          6. Расторжение, как и заключение договора, должно быть зарегистрировано.<br><br>
          Дружеское или судебное разбирательство должно быть юридически оформлено. Разочарование и неудача
          не являются доказательством. Размер, степень и форма ущерба должны быть проиллюстрированы фактами
          и цифрами.</p>
        <h4>Другие способы закрыть франшизу</h4>
        <p>Мирным решением проблемы должно стать официальное расторжение договора или его расторжение. Лучше,
          если это произойдет по обоюдному согласию. Судебные разбирательства часто бывают длительными,
          эмоционально и финансово изматывающими. Однако отказ от франшизы является законным в <span class="blackText">следующих
            случаях:</span>
          <br> <br>
          • Ликвидация предприятия-франчайзи; <br> <br>
          • Банкротство сторон (в том числе доказанное банкротство франшизы); <br> <br>
          • Изменение / недействительность коммерческой части бренда (наименования, любого из объектов
          исключительных прав); <br> <br>
          • Потеря авторских прав франшизой; <br> <br>
          • Ошибки при исполнении договора (например, обязательная регистрация договора не была завершена).
        </p>
        <h4>Продажа франшизы</h4>
        <p>Все опять упирается в договор и в то, что франчайзер обязательно должен знать о планах по продаже.
          Есть много параллелей в процессах «продать свой бизнес» и " продать свой бизнес как франшизу».
          Обязательно сначала:<br> <br>
          • Оговаривается ли в договоре такой вариант, имеет ли франчайзи право на перепродажу?<br> <br>
          • Готова ли франчайзинговая точка к продаже и упакована ли она в соответствии с критериями
          франчайзинга?<br> <br>
          • Является ли причина продажи точно соответствующей предложению о покупке и тщательно ли она
          сформулирована?<br> <br>
          • Планируется ли маркетинговая и юридическая часть продажи?<br> <br>
          • Хочет ли франчайзер купить этот товар?</p>
        <h4>Как получить компенсацию за франшизу</h4>
        <p>Бывают крайние случаи: вы расторгли договор, подали заявление о компенсации, предусмотренной
          договором, но взамен ничего не получите. Адвокат подал соответствующий иск, в котором правильно
          указаны документально подтвержденные факты нарушения конкретных положений договора. Франчайзер
          не выполнил, деградировал, не поставил, а вы определили и доказали сумму претензий, но ничего
          не происходит. Тогда может помочь поддержка местного сообщества предпринимателей и франчайзеров,
          общественного мнения и других франчайзи.<br> <br>
          Когда все юридические аспекты будут учтены, самое время начать публичную кампанию:<br> <br>
          • Обращение в надзорные органы;<br> <br>
          • Публикация негативных отзывов в официальных каталогах франшиз;<br> <br>
          • Работа со средствами массовой информации.<br> <br>
          <span class="blackText">Важно понимать</span>, что мошенники ничего не отрицают, и мир франчайзинга
          от них не ускользнул. К сожалению, в случае покупки «мошеннической франшизы» компенсация часто
          оказывается невозможной. Хотя все обиженные франчайзи объединяются и могут доказать мошенничество
          в суде. Но одним мошенником в бегах станет меньше.
        </p>
      </div>
    </div>
  </div>
</div>
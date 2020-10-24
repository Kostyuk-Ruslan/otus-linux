
<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе
 * установки. Необязательно использовать веб-интерфейс, можно
 * скопировать файл в "wp-config.php" и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define( 'DB_NAME', 'wordpress' );

/** Имя пользователя MySQL */
define( 'DB_USER', 'username' );

/** Пароль к базе данных MySQL */
define( 'DB_PASSWORD', 'password' );

/** Имя сервера MySQL */
define( 'DB_HOST', 'localhost' );

/** Кодировка базы данных для создания таблиц. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Схема сопоставления. Не меняйте, если не уверены. */
define( 'DB_COLLATE', '' );

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу.
 * Можно сгенерировать их с помощью {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными. Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '3L{90.q$EP_ok] [hIi;FX|Mf.]Q@y?f-wzFd5p[XIb2F:#!v|B;.-y>bKjZ_pp;' );
define( 'SECURE_AUTH_KEY',  '?^A}92CGuTT??c2|Xp&r(SCmx9z+eqM8gS|M$+=|.PB:C7BAF8xj1y`uBU%eaVo8' );
define( 'LOGGED_IN_KEY',    'rXN.ENBkHujl^[s47F6!Wnznpki&Hd;KaQW`d@[Mo#Y>^*{pC9IxBM3l d]Ki #V' );
define( 'NONCE_KEY',        'wI+Y^f%V9a<`Bd6Z^H+OYn]lh*mWW8xhXJ#%j:?w1BhD L{-4t5ak{px_wMDzdrN' );
define( 'AUTH_SALT',        'UHF/L?Z~(1;L{moJG+ndy&M~]SBGd[qJt&GSS3cbJGH8v8XR @Jh.+JcTqp?K]}8' );
define( 'SECURE_AUTH_SALT', 'KWRj8)nhu: @N~*n93o#?wBW:33*{w:B5(Il8aca3Wa;bfkhkH$a?VLs.t&JdMbB' );
define( 'LOGGED_IN_SALT',   'x%+P:!HaXM[Gy$N*@MsWU!u?K~2`k1OG`>%Q.U`7k2sf[DR9,BG3BV+R9QJu*o>*' );
define( 'NONCE_SALT',       'P(nx6>SYogzH.nn0<2OJ:7$Y>PY0#Q8/h3.1/2>f+CRC%S!dX xU2Fqk9]=[6o>x' );

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 *
 * Информацию о других отладочных константах можно найти в Кодексе.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Инициализирует переменные WordPress и подключает файлы. */
require_once( ABSPATH . 'wp-settings.php' );


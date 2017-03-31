# SVG Icon Workflow

## Icons lagern und verarbeiten
Wenn ich nicht gezwungen bin Icons selbst zu gestalten (oder gestalten zu lassen), greife ich auf einige Projekte zurück.

* [Nova Icons](http://www.webalys.com/nova/) habe ich vor einer Weile mal günstig geschossen. Umfangreich, und simpel im Stil.
* [Geomicons](https://github.com/jxnblk/geomicons-open) ist eine Sammlung von simplen SVG-Icons die wenige Pfadpunkte besitzen und so den Traffic klein halten.
* [Bytesize Icons](https://github.com/danklammer/bytesize-icons) ist ähnlich wie Geomicons, nur mit einem anderen Stil.
* Icons von [FontAwesome](https://github.com/FortAwesome/Font-Awesome) verwende ich immer als letzte Wahl, da dieses aktuell Set quasi der Standard im Web ist. Ich bin als Backer der Kickstarter-Kampagne sehr gespannt auf Font Awesome 5 (Pro).

Wenn ich das richtige Icon gefunden habe, erzeuge ich im Illustrator eine neue Datei mit den Maßen 128×128 Pixel und platziere das Icon. Ich stelle die Farbe des Icons (oder von einzelnen Teilen) auf schwarz (#000 - das ist später noch wichtig) und speichere es.

Dabei verwende ich in meinen Projekten eine Ordnerstruktur wie die Folgende.

````
.
+-- assets/                 Arbeitsdateien
|   +-- icons/                  SVG Icons
|   +-- img/                    Bilder
|   +-- js/                     Magic
|   +-- less/                   Styling
+-- dist/                   Build Dateien
|   +-- css/                    Kompilierte CSS Dateien
|   +-- icons/                  Optimierte Kopie der Icons (für PHP Check)
|   +-- img/                    Optimierte Bilder
|   +-- js/                     JS Dateien und Polyfills
|   +-- sprite/                 SVG Sprite und Übersicht
````

Für Viele ist es sicher gängige Praxis optimierte und kompilierte Ressourcen in einem ``dist`` Ordner zu lagern und den ``assets`` Ordner nicht auf den Live-Server zu kopieren.

Das Icon wird von ``Grunt`` mittels [grunt-svg-sprite](https://github.com/jkphl/grunt-svg-sprite) ins Sprite integriert und mit [grunt-svgmin](https://github.com/sindresorhus/grunt-svgmin) eine optimierte Kopie in ``dist/icons`` abgelegt. Diese Kopie nutzen wir später in PHP um zu prüfen, ob eine übergebene ID (Icon-Datei-Name) auch wirklich im Sprite zu finden sein wird.

``grunt-svg-sprite`` erzeugt auch eine [HTML-Datei](http://svg.sebastianlaube.de/dist/sprite/sprite.html) in der z.B. Redakteure die IDs nachschlagen können. Die Vorlage dazu liegt in ``assets/icons``.

## Der Code
````
function get_svg_icon( $id, $atts = array() ) {
    // 1. do we have the reuired parameter?
    if ( empty( $id ) ) {
        if ( WP_DEBUG == true ) {
            return 'Icon ID fehlt.';
        }

        return;
    }

    // 2. get some attributes
    extract( shortcode_atts(
        array(
            'class' => null,
            'title' => null
            // maybe we want to add desc (<desc>) for even more a11y
        ),
        $atts
    ));

    // 3. check if this ID will be in the sprite
    if ( ! file_exists( TEMPLATEPATH . '/dist/icons/' . $id . '.svg' ) ) {
        if ( WP_DEBUG == true ) {
            return 'Icon nicht vorhanden.';
        }

        return;
    }

    // 4. will we add extra CSS classes?
    $att_class = ( empty( $class ) ) ? '' : ' ' . $class;

    // 5. create a unique ID
    $att_id = uniqid( 'icon__title--' );

    // 6. additional markup attributes for a11y
    // is this icon only decorative (hidden) or descriptive (title)
    $att_a11y = ( empty( $title ) ) ? ' aria-hidden="true" role="presentation"' : ' aria-labelledby="' . $att_id . '" role="img"';
    $title = ( empty( $title ) ) ? '' : '<title id="' . $att_id . '">' . $title . '</title>';

    // 7. puzzle the URL together
    $url = get_template_directory_uri() . '/dist/sprite/sprite.svg#' . $id;

    // 8. let's roll!
    return '<svg class="icon' . $att_class . '"' . $att_a11y . '>' . $title . '<use xlink:href="' . $url . '"></use></svg>';
}

function svg_icon( $id, $atts = array() ) {
    echo get_svg_icon( $id, $atts );
}
````

Wie sich für ein geübtes Auge sofort erkennen lässt, befinden wir uns im Kontext von WordPress. Prinzipiell lässt sich das aber auch ohne WordPress machen, dazu ändert man die Verarbeitung der Attribute, die Konstanten und  die Erzeugung der URL.

1. Prüfen, ob eine ID übergeben wurde. Wenn nicht brechen wir ab.
2. Extrahieren die Attribute
3. Prüfen, ob es ein Icon mit der ID gibt. Wenn nicht brechen wir ab.
4. Extra CSS-Klassen vorbereiten
5. Vorsorglich eine eindeutige ID erzeugen
6. Ist ein Titel in den Attributen definiert, wird dieser dem SVG hinzugefügt und als Label referenziert (für Screenreader/Tooltip). Andernfalls wird das SVG als dekorativ gekennzeichnet.
7. Die URL setzt sich auf der URL des Theme-Ordners, dem Pfad zum Sprite und der ID zusammen.
8. Zum Schluss wird alles zusammengefügt und übergeben.

Verwendet wird das Ganze dann recht simpel:
````
    <div class="contact">
        <?php svg_icon( 'phone', array( 'title' => 'Telefon' ) ) ?> 0123 456 78 90

        <?php svg_icon( 'mail', array( 'title' => 'E-Mail', 'class' => 'icon--blue' ) ) ?> foo@bar.foo
    </div>

````

Alternativ geht das auch als WordPress-Shortcode
````
    [icon phone title="Telefon"] 0123 456 78 90
````

Die Ausgabe sieht dann etwa so aus:
````
    <svg class="icon" aria-labelledby="icon__title--58de43ac12ead" role="img">
        <title id="icon__title--58de43ac12ead">Telefon</title>
        <use xlink:href="http://foo.bar/wp-content/themes/mytheme/dist/sprite/sprite.svg#phone"></use>
    </svg>
````

Moderne Browser unterstützen die Referenzierung einer SVG-Datei via ``xlink:href`` und es ist nicht nötig das Sprite irgendwo im Dokument zu <q>includen</q> (ideal fürs Caching). …Außer beim Problemkind von Microsoft (IE 9+), hier wird der Polyfill [svgxuse](https://github.com/Keyamoon/svgxuse) benötigt. Der Polyfill holt sich das Sprite, schreibt es in den DOM und löscht den URL-Teil aus den ``<use>``-Tags.

## Styling
Jetzt haben wir zwar die Icons elegant eingebunden, aber sie müssen noch elegant gestylt werden.

````
/* 1 */
.icon {
    fill: currentColor;
    height: 1em;
    width: 1em;
    overflow: hidden;
    vertical-align: -.15em;
    /* vertical align to deal with the line height of the text */
}

/* 2 */
.icon--blue {
    color:#07f;
}
/* 3 */
.icon--logo rect {
    fill: rebeccapurple;
}
````

Ist bei einem SVG-Element kein ``fill``-Attribut gesetzt, verwendet der Browser Schwarz als Fallback. ``imagemin`` entfernt das ``fill``-Attribut, wenn es als Schwarz (``#000`` oder ``#000000``) definiert ist aus diesem Grund. 

Das machen wir uns nun zu nutze und sagen dem Icon es soll die Schriftfarbe (``color``) des Elternelements nutzen (1).

Wollen wir es einfärben, können wir es direkt ansprechen und eine Farbe definieren (2). Obwohl die SVG-Grafik nur mit einer URL referenziert ist, können wir die darin enthaltenen Elemente auch via CSS ansprechen, als wäre es fester Teil des DOM (3).

Die Icons sind quadratisch angelegt. Wenn man aber z. B. ein Logo mit speziellem Seitenverhältnis einbinden möchte, regelt man das am Besten über die zusätzliche CSS Klasse. Gegebenenfalls könnte man die ``get_svg_icon``-Funktion manipulieren um bei bestimmten IDs automatisch eine bestimmte Klasse mit einzufügen.

Das Endergebnis gibts natürlich auch als [Demo](http://svg.sebastianlaube.de).

Ich freue mich auf Feedback. Wer Rechtschreibfehler findet, kann sie behalten.
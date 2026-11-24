<?php

namespace Database\Seeders;

use App\Data\QuestionDraft;
use App\Data\QuizDraft;
use App\Enums\PopColor;

/**
 * The quizzes of the house. The right choice comes first: each attempt shuffles
 * them.
 */
final readonly class HouseQuizzes
{
    /**
     * @return list<QuizDraft>
     */
    public static function all(): array
    {
        return [
            new QuizDraft('Emoji Cinema', '🎬', PopColor::Pink, 'Three emojis, one movie. Popcorn not included.', [
                new QuestionDraft('🦁 👑 🌅', ['The Lion King', 'Madagascar', 'The Jungle Book', 'Zootopia'], 0, 'Disney, 1994: its working title was King of the Jungle, until someone remembered that lions live in the savanna.'),
                new QuestionDraft('🚢 🧊 💔', ['Titanic', 'Life of Pi', 'Frozen', 'Pirates of the Caribbean'], 0, 'James Cameron, 1997: it won 11 Oscars, a record it shares with Ben-Hur and The Return of the King.'),
                new QuestionDraft('🦖 🏝️ 🚙', ['Jurassic Park', 'King Kong', 'Ice Age', 'The Land Before Time'], 0, 'Steven Spielberg, 1993: only a few minutes of its dinosaurs are computer graphics, the others are animatronics.'),
                new QuestionDraft('👽 🚲 🌕', ['E.T. the Extra-Terrestrial', 'Men in Black', 'Close Encounters of the Third Kind', 'Independence Day'], 0, 'Steven Spielberg, 1982: "E.T. phone home."'),
                new QuestionDraft('🐠 🔎 🌊', ['Finding Nemo', 'The Little Mermaid', 'Shark Tale', 'Moana'], 0, 'Pixar, 2003: Nemo is a clownfish, Dory a blue tang.'),
                new QuestionDraft('🤖 🌱 ❤️', ['WALL·E', 'Big Hero 6', 'Iron Giant', 'Robots'], 0, 'Pixar, 2008: WALL·E stands for Waste Allocation Load Lifter: Earth-Class.'),
                new QuestionDraft('🧙 💍 🌋', ['The Lord of the Rings', 'Harry Potter', 'The Wizard of Oz', 'Fantasia'], 0, 'One ring to rule them all, thrown into Mount Doom.'),
                new QuestionDraft('🦈 🏖️ 🎻', ['Jaws', 'The Meg', 'Finding Nemo', 'Free Willy'], 0, 'Steven Spielberg, 1975: the crew nicknamed the mechanical shark Bruce.'),
            ]),
            new QuizDraft('Elephant Facts', '🐘', PopColor::Violet, 'PHP, from a few scripts in 1994 to the tab you are reading this in.', [
                new QuestionDraft('Who created PHP?', ['Rasmus Lerdorf', 'Linus Torvalds', 'Guido van Rossum', 'Brendan Eich'], 0, 'In 1994, to keep track of who visited his online résumé.'),
                new QuestionDraft('What does PHP stand for today?', ['PHP: Hypertext Preprocessor', 'Personal Home Page', 'Pretty Handy Programming', 'Preprocessed Hypertext Pages'], 0, 'A recursive acronym. Personal Home Page was its first name.'),
                new QuestionDraft('Which version of PHP never came out?', ['PHP 6', 'PHP 5', 'PHP 7', 'PHP 8'], 0, 'Its Unicode rewrite was abandoned: PHP 5.6 was followed by PHP 7, in 2015.'),
                new QuestionDraft('What color is the elePHPant, the mascot of PHP?', ['Blue', 'Pink', 'Grey', 'Green'], 0, 'Drawn by Vincent Pontier. It now comes in many colors, the first one is blue.'),
                new QuestionDraft('Which version brought the JIT compiler?', ['PHP 8.0', 'PHP 7.4', 'PHP 8.1', 'PHP 8.3'], 0, 'PHP 8.0, released in November 2020, with named arguments and attributes too.'),
                new QuestionDraft('What is the nickname of the <=> operator?', ['The spaceship', 'The diamond', 'The fish', 'The arrowhead'], 0, 'It compares two values, and gives -1, 0 or 1. It came with PHP 7.'),
                new QuestionDraft('Who created Laravel, the framework that runs this quiz?', ['Taylor Otwell', 'Fabien Potencier', 'Jordi Boggiano', 'Rasmus Lerdorf'], 0, 'Its first version came out in 2011.'),
                new QuestionDraft('Where is the PHP of this quiz running?', ['In this browser tab', 'On a server in the cloud', 'On a Raspberry Pi', 'Nowhere, it is plain HTML'], 0, 'PHP is compiled to WebAssembly: no server runs it, and the database is in your browser.'),
            ]),
            new QuizDraft('Pop Art Pop Quiz', '🎨', PopColor::Yellow, 'Soup cans, comic dots and loud colors.', [
                new QuestionDraft('Who painted the Campbell\'s Soup Cans?', ['Andy Warhol', 'Roy Lichtenstein', 'Keith Haring', 'Jackson Pollock'], 0, '32 canvases, one for each flavor sold in 1962.'),
                new QuestionDraft('The dots of Roy Lichtenstein copy the printing of…', ['Comic books', 'Postage stamps', 'Television screens', 'Wallpaper'], 0, 'They are Ben-Day dots: cheap printing, made into art.'),
                new QuestionDraft('Red and blue paint, mixed, give…', ['Purple', 'Green', 'Orange', 'Brown'], 0, 'Red and yellow give orange, blue and yellow give green.'),
                new QuestionDraft('Which colors does a screen mix to make all the others?', ['Red, green and blue', 'Red, yellow and blue', 'Cyan, magenta and yellow', 'Black and white'], 0, 'Light adds up: all three give white. Printers mix cyan, magenta and yellow.'),
                new QuestionDraft('Who made the giant, shiny Balloon Dog sculptures?', ['Jeff Koons', 'Banksy', 'Yayoi Kusama', 'Takashi Murakami'], 0, 'They are stainless steel, not balloons.'),
                new QuestionDraft('Which one is pure yellow, in CSS?', ['#FFFF00', '#00FFFF', '#FF00FF', '#FFFFFF'], 0, 'Red and green at full, no blue. #00FFFF is cyan, #FF00FF magenta.'),
                new QuestionDraft('"In the future, everyone will be world-famous for…"', ['15 minutes', '15 seconds', '1 hour', 'a whole day'], 0, 'A line credited to Andy Warhol, in 1968.'),
                new QuestionDraft('Which color is opposite orange on the color wheel?', ['Blue', 'Green', 'Purple', 'Red'], 0, 'Complementary colors make each other pop: think of a sunset over the sea.'),
            ]),
            new QuizDraft('Around the World', '🌍', PopColor::Cyan, 'Capitals, oceans and places to put on your list.', [
                new QuestionDraft('What is the capital of Australia?', ['Canberra', 'Sydney', 'Melbourne', 'Perth'], 0, 'It was built to settle the rivalry between Sydney and Melbourne.'),
                new QuestionDraft('Which country has the most time zones?', ['France', 'Russia', 'United States', 'China'], 0, 'Thanks to its overseas territories, from French Polynesia to New Caledonia.'),
                new QuestionDraft('What is the smallest country in the world?', ['Vatican City', 'Monaco', 'San Marino', 'Liechtenstein'], 0, 'About half a square kilometer, inside Rome.'),
                new QuestionDraft('What is the highest mountain above sea level?', ['Mount Everest', 'K2', 'Kilimanjaro', 'Mont Blanc'], 0, 'About 8,849 meters, on the border of Nepal and China.'),
                new QuestionDraft('Which ocean is the largest?', ['The Pacific', 'The Atlantic', 'The Indian Ocean', 'The Arctic Ocean'], 0, 'It is bigger than all the land of the Earth put together.'),
                new QuestionDraft('What is the largest hot desert?', ['The Sahara', 'The Gobi', 'The Kalahari', 'The Atacama'], 0, 'Counting the cold ones, Antarctica is bigger.'),
                new QuestionDraft('In which country is Machu Picchu?', ['Peru', 'Mexico', 'Bolivia', 'Chile'], 0, 'An Inca citadel of the 15th century, high in the Andes.'),
                new QuestionDraft('What is the capital of Canada?', ['Ottawa', 'Toronto', 'Montreal', 'Vancouver'], 0, 'Queen Victoria chose it, in 1857.'),
            ]),
        ];
    }
}

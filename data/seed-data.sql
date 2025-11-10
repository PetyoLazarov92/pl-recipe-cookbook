-- ============================================
-- Seed Data for PL Recipe Cookbook
-- Categories and Common Bulgarian Ingredients
-- ============================================

-- Categories
INSERT INTO {prefix}pl_ingredient_categories (name, name_en, slug, display_order) VALUES
('месо и колбаси', 'meat & sausages', 'meat', 1),
('зеленчуци', 'vegetables', 'vegetables', 2),
('плодове', 'fruits', 'fruits', 3),
('млечни продукти', 'dairy', 'dairy', 4),
('зърнени храни', 'grains', 'grains', 5),
('подправки и билки', 'spices & herbs', 'spices', 6),
('течности', 'liquids', 'liquids', 7),
('ядки и семена', 'nuts & seeds', 'nuts-seeds', 8),
('мазнини и олио', 'fats & oils', 'fats-oils', 9),
('сладкиши и захар', 'sweets & sugar', 'sweets', 10);

-- Meat & Sausages
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(1, 'пилешко месо', 'chicken', 'pileshko-meso'),
(1, 'свинско месо', 'pork', 'svinsko-meso'),
(1, 'телешко месо', 'beef', 'teleshko-meso'),
(1, 'агнешко месо', 'lamb', 'agneshko-meso'),
(1, 'кайма', 'minced meat', 'kayma'),
(1, 'пилешки гърди', 'chicken breast', 'pileshki-gardi'),
(1, 'пилешки бутчета', 'chicken drumsticks', 'pileshki-butcheta'),
(1, 'свински ребра', 'pork ribs', 'svinski-rebra'),
(1, 'шунка', 'ham', 'shunka'),
(1, 'бекон', 'bacon', 'bekon'),
(1, 'наденица', 'sausage', 'nadenitsa'),
(1, 'салам', 'salami', 'salam'),
(1, 'луканка', 'lukanka', 'lukanka');

-- Vegetables
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(2, 'домати', 'tomatoes', 'domati'),
(2, 'краставици', 'cucumbers', 'krastavitsi'),
(2, 'чушки', 'peppers', 'chushki'),
(2, 'лук', 'onion', 'luk'),
(2, 'чесън', 'garlic', 'chesen'),
(2, 'картофи', 'potatoes', 'kartofi'),
(2, 'моркови', 'carrots', 'morkovi'),
(2, 'зеле', 'cabbage', 'zele'),
(2, 'тиквички', 'zucchini', 'tikvichki'),
(2, 'патладжан', 'eggplant', 'patladjan'),
(2, 'спанак', 'spinach', 'spanak'),
(2, 'броколи', 'broccoli', 'brokoli'),
(2, 'карфиол', 'cauliflower', 'karfiol'),
(2, 'маруля', 'lettuce', 'marulya'),
(2, 'магданоз', 'parsley', 'magdanoz'),
(2, 'копър', 'dill', 'kopar'),
(2, 'целина', 'celery', 'tselina'),
(2, 'праз лук', 'leeks', 'praz-luk'),
(2, 'грах', 'peas', 'grah'),
(2, 'боб', 'beans', 'bob'),
(2, 'леща', 'lentils', 'leshta'),
(2, 'нахут', 'chickpeas', 'nahut');

-- Fruits
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(3, 'ябълки', 'apples', 'yabalkи'),
(3, 'круши', 'pears', 'krushi'),
(3, 'банани', 'bananas', 'banani'),
(3, 'портокали', 'oranges', 'portokali'),
(3, 'лимони', 'lemons', 'limoni'),
(3, 'ягоди', 'strawberries', 'yagodi'),
(3, 'малини', 'raspberries', 'malini'),
(3, 'боровинки', 'blueberries', 'borovinki'),
(3, 'череши', 'cherries', 'chereshi'),
(3, 'кайсии', 'apricots', 'kaysii'),
(3, 'праскови', 'peaches', 'praskovi'),
(3, 'грозде', 'grapes', 'grozde'),
(3, 'диня', 'watermelon', 'dinya'),
(3, 'пъпеш', 'melon', 'papesh');

-- Dairy
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(4, 'сирене', 'white cheese', 'sirene'),
(4, 'кашкавал', 'yellow cheese', 'kashkaval'),
(4, 'прясно мляко', 'fresh milk', 'prqsno-mlqko'),
(4, 'кисело мляко', 'yogurt', 'kiselo-mlqko'),
(4, 'масло', 'butter', 'maslo'),
(4, 'яйца', 'eggs', 'yaytsa'),
(4, 'извара', 'cottage cheese', 'izvara'),
(4, 'сметана', 'sour cream', 'smetana'),
(4, 'заквaска', 'bulgarian yogurt starter', 'zakvasaka'),
(4, 'крема сирене', 'cream cheese', 'krema-sirene'),
(4, 'моцарела', 'mozzarella', 'mozarela'),
(4, 'пармезан', 'parmesan', 'parmezan');

-- Grains
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(5, 'брашно', 'flour', 'brashno'),
(5, 'ориз', 'rice', 'oriz'),
(5, 'макарони', 'pasta', 'makaroni'),
(5, 'спагети', 'spaghetti', 'spageti'),
(5, 'овесени ядки', 'oats', 'oveseni-yadki'),
(5, 'ечемик', 'barley', 'echemik'),
(5, 'царевица', 'corn', 'tsarevitsa'),
(5, 'квиноа', 'quinoa', 'kvinoa'),
(5, 'булгур', 'bulgur', 'bulgur'),
(5, 'кус кус', 'couscous', 'kus-kus'),
(5, 'хляб', 'bread', 'hlyab'),
(5, 'галета', 'crackers', 'galeta');

-- Spices & Herbs
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(6, 'сол', 'salt', 'sol'),
(6, 'черен пипер', 'black pepper', 'cheren-piper'),
(6, 'червен пипер', 'red pepper', 'cherven-piper'),
(6, 'чубрица', 'savory', 'chubritsa'),
(6, 'босилек', 'basil', 'bosilek'),
(6, 'риган', 'oregano', 'rigan'),
(6, 'мащерка', 'thyme', 'mashterka'),
(6, 'кимион', 'cumin', 'kimion'),
(6, 'джинджифил', 'ginger', 'djindjifjl'),
(6, 'кари', 'curry', 'kari'),
(6, 'канела', 'cinnamon', 'kanela'),
(6, 'ванилия', 'vanilla', 'vaniliya'),
(6, 'дафинов лист', 'bay leaf', 'dafinov-list'),
(6, 'лют пипер', 'chili pepper', 'lyut-piper');

-- Liquids
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(7, 'вода', 'water', 'voda'),
(7, 'бульон', 'broth', 'bulyon'),
(7, 'доматено пюре', 'tomato paste', 'domateno-pyure'),
(7, 'доматен сок', 'tomato juice', 'domaten-sok'),
(7, 'соев сос', 'soy sauce', 'soev-sos'),
(7, 'оцет', 'vinegar', 'otset'),
(7, 'лимонов сок', 'lemon juice', 'limonov-sok'),
(7, 'бяло вино', 'white wine', 'byalo-vino'),
(7, 'червено вино', 'red wine', 'cherveno-vino'),
(7, 'ракия', 'rakia', 'rakiya'),
(7, 'мед', 'honey', 'med');

-- Nuts & Seeds
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(8, 'орехи', 'walnuts', 'orehi'),
(8, 'бадеми', 'almonds', 'bademi'),
(8, 'лешници', 'hazelnuts', 'leshnitsi'),
(8, 'фъстъци', 'peanuts', 'fastatsи'),
(8, 'кашу', 'cashews', 'kashu'),
(8, 'слънчогледови семки', 'sunflower seeds', 'slanchogledovi-semki'),
(8, 'тиквени семки', 'pumpkin seeds', 'tikveni-semki'),
(8, 'сусам', 'sesame', 'susam'),
(8, 'чия семена', 'chia seeds', 'chia-semena'),
(8, 'ленено семе', 'flax seeds', 'leneno-seme');

-- Fats & Oils
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(9, 'слънчогледово олио', 'sunflower oil', 'slanchogledovo-olio'),
(9, 'зехтин', 'olive oil', 'zehtin'),
(9, 'масло', 'butter', 'maslo-oil'),
(9, 'маргарин', 'margarine', 'margarin'),
(9, 'свинска мас', 'lard', 'svinska-mas');

-- Sweets & Sugar
INSERT INTO {prefix}pl_ingredients (category_id, name, name_en, slug) VALUES
(10, 'захар', 'sugar', 'zahar'),
(10, 'кафява захар', 'brown sugar', 'kafyava-zahar'),
(10, 'пудра захар', 'powdered sugar', 'pudra-zahar'),
(10, 'мед', 'honey', 'med-sweet'),
(10, 'шоколад', 'chocolate', 'shokolad'),
(10, 'какао', 'cocoa', 'kakao'),
(10, 'бакпулвер', 'baking powder', 'bakpulver'),
(10, 'сода', 'baking soda', 'soda'),
(10, 'мая', 'yeast', 'maya');

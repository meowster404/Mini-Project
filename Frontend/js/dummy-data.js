const dummyProducts = [
    // Vegetables (30 items)
    ...Array(30).fill(null).map((_, index) => ({
        id: index + 1,
        name: [
            'Fresh Tomatoes', 'Organic Spinach', 'Green Bell Peppers', 'Red Onions', 
            'Baby Carrots', 'Broccoli Crowns', 'Cauliflower', 'Sweet Potatoes',
            'Green Beans', 'Zucchini', 'Yellow Squash', 'Red Potatoes',
            'Brussels Sprouts', 'Asparagus', 'Celery', 'Cucumber',
            'Romaine Lettuce', 'Red Cabbage', 'Mushrooms', 'Eggplant',
            'Green Peas', 'Sweet Corn', 'Artichokes', 'Kale Bunches',
            'Swiss Chard', 'Radishes', 'Turnips', 'Beets',
            'Garlic Bulbs', 'Ginger Root'
        ][index],
        description: `Fresh and locally sourced ${['Fresh Tomatoes', 'Organic Spinach', 'Green Bell Peppers', 'Red Onions', 'Baby Carrots'][index % 5]} from trusted farmers.`,
        price: Math.floor(Math.random() * (200 - 20) + 20),
        image: `/assets/image/products/vegetables/${index + 1}.jpg`,
        category: 'vegetables',
        farm_name: ['Green Valley Farm', 'Organic Valley', 'Fresh Fields', 'Nature\'s Best', 'Sunshine Farms'][index % 5],
        is_organic: Math.random() > 0.5,
        discount: Math.random() > 0.7 ? Math.floor(Math.random() * 20) : 0,
        farming_method: ['organic', 'conventional', 'hydroponic'][Math.floor(Math.random() * 3)],
        stock: Math.floor(Math.random() * 100) + 10,
        popularity: Math.floor(Math.random() * 40) + 60
    })),

    // Fruits (30 items)
    ...Array(30).fill(null).map((_, index) => ({
        id: index + 31,
        name: [
            'Golden Bananas', 'Red Apples', 'Sweet Oranges', 'Green Grapes', 
            'Fresh Strawberries', 'Ripe Mangoes', 'Juicy Peaches', 'Red Cherries',
            'Sweet Pineapple', 'Fresh Blueberries', 'Green Kiwi', 'Red Pomegranate',
            'Yellow Lemons', 'Lime', 'Dragon Fruit', 'Fresh Figs',
            'Green Pears', 'Sweet Plums', 'Fresh Raspberries', 'Blackberries',
            'Watermelon', 'Cantaloupe', 'Honeydew Melon', 'Fresh Coconut',
            'Sweet Papaya', 'Fresh Guava', 'Passion Fruit', 'Fresh Lychee',
            'Sweet Apricots', 'Fresh Dates'
        ][index],
        description: `Sweet and fresh ${['Bananas', 'Apples', 'Oranges', 'Grapes', 'Strawberries'][index % 5]} picked at peak ripeness.`,
        price: Math.floor(Math.random() * (300 - 40) + 40),
        image: `/assets/image/products/fruits/${index + 1}.jpg`,
        category: 'fruits',
        farm_name: ['Fruit Valley', 'Sweet Orchards', 'Fresh Picks', 'Nature\'s Bounty', 'Sunny Farms'][index % 5],
        is_organic: Math.random() > 0.5,
        discount: Math.random() > 0.7 ? Math.floor(Math.random() * 20) : 0,
        farming_method: ['organic', 'conventional'][Math.floor(Math.random() * 2)],
        stock: Math.floor(Math.random() * 100) + 10,
        popularity: Math.floor(Math.random() * 40) + 60
    })),

    // Meat (30 items)
    ...Array(30).fill(null).map((_, index) => ({
        id: index + 61,
        name: [
            'Chicken Breast', 'Ground Beef', 'Pork Chops', 'Lamb Chops', 
            'Turkey Breast', 'Beef Sirloin', 'Chicken Wings', 'Pork Ribs',
            'Ground Turkey', 'Beef Tenderloin', 'Chicken Thighs', 'Pork Belly',
            'Lamb Rack', 'Duck Breast', 'Chicken Drumsticks', 'Beef Ribeye',
            'Ground Pork', 'Veal Cutlets', 'Turkey Wings', 'Beef Brisket',
            'Chicken Whole', 'Pork Tenderloin', 'Ground Lamb', 'Turkey Thighs',
            'Beef Chuck', 'Pork Sausage', 'Lamb Shoulder', 'Turkey Drumsticks',
            'Beef Short Ribs', 'Chicken Liver'
        ][index],
        description: `Premium quality ${['Chicken', 'Beef', 'Pork', 'Lamb', 'Turkey'][index % 5]} from free-range farms.`,
        price: Math.floor(Math.random() * (800 - 150) + 150),
        image: `/assets/image/products/meat/${index + 1}.jpg`,
        category: 'meat',
        farm_name: ['Green Pastures', 'Happy Animals', 'Free Range Farm', 'Quality Meats', 'Natural Farms'][index % 5],
        is_organic: Math.random() > 0.5,
        discount: Math.random() > 0.7 ? Math.floor(Math.random() * 20) : 0,
        farming_method: ['free-range', 'conventional'][Math.floor(Math.random() * 2)],
        stock: Math.floor(Math.random() * 50) + 10,
        popularity: Math.floor(Math.random() * 40) + 60
    })),

    // Eggs (30 items)
    ...Array(30).fill(null).map((_, index) => ({
        id: index + 91,
        name: [
            'Brown Eggs', 'White Eggs', 'Free Range Eggs', 'Organic Eggs', 
            'Duck Eggs', 'Quail Eggs', 'Jumbo Eggs', 'Farm Fresh Eggs',
            'Cage-Free Eggs', 'Large Eggs', 'Medium Eggs', 'Extra Large Eggs',
            'Omega-3 Eggs', 'Pastured Eggs', 'Heritage Eggs', 'Local Farm Eggs',
            'Premium Eggs', 'Grade A Eggs', 'Country Eggs', 'Barn Eggs',
            'Natural Eggs', 'Fresh Eggs', 'Quality Eggs', 'Select Eggs',
            'Choice Eggs', 'Golden Eggs', 'Specialty Eggs', 'Value Pack Eggs',
            'Family Pack Eggs', 'Premium Select Eggs'
        ][index],
        description: `Fresh ${['Brown', 'White', 'Free Range', 'Organic', 'Farm Fresh'][index % 5]} eggs from happy hens.`,
        price: Math.floor(Math.random() * (200 - 50) + 50),
        image: `/assets/image/products/eggs/${index + 1}.jpg`,
        category: 'eggs',
        farm_name: ['Happy Hens', 'Fresh Eggs Farm', 'Organic Nest', 'Natural Eggs', 'Free Range Valley'][index % 5],
        is_organic: Math.random() > 0.5,
        discount: Math.random() > 0.7 ? Math.floor(Math.random() * 20) : 0,
        farming_method: ['free-range', 'organic', 'conventional'][Math.floor(Math.random() * 3)],
        stock: Math.floor(Math.random() * 100) + 20,
        popularity: Math.floor(Math.random() * 40) + 60
    })),

    // Dairy (30 items)
    ...Array(30).fill(null).map((_, index) => ({
        id: index + 121,
        name: [
            'Whole Milk', 'Greek Yogurt', 'Cheddar Cheese', 'Butter', 
            'Heavy Cream', 'Cottage Cheese', 'Mozzarella', 'Sour Cream',
            'Cream Cheese', 'Swiss Cheese', 'Low-Fat Milk', 'Plain Yogurt',
            'Gouda Cheese', 'Whipped Cream', 'Buttermilk', 'String Cheese',
            'Parmesan Cheese', 'Half and Half', 'Ricotta Cheese', 'Provolone',
            'Chocolate Milk', 'Vanilla Yogurt', 'Blue Cheese', 'Feta Cheese',
            'Skim Milk', 'Flavored Yogurt', 'Brie Cheese', 'Mascarpone',
            'Organic Milk', 'Farm Cheese'
        ][index],
        description: `Fresh ${['Milk', 'Yogurt', 'Cheese', 'Cream', 'Butter'][index % 5]} from local dairy farms.`,
        price: Math.floor(Math.random() * (300 - 30) + 30),
        image: `/assets/image/products/dairy/${index + 1}.jpg`,
        category: 'dairy',
        farm_name: ['Dairy Fresh', 'Green Meadows', 'Pure Dairy', 'Fresh Farms', 'Nature\'s Dairy'][index % 5],
        is_organic: Math.random() > 0.5,
        discount: Math.random() > 0.7 ? Math.floor(Math.random() * 20) : 0,
        farming_method: ['organic', 'conventional'][Math.floor(Math.random() * 2)],
        stock: Math.floor(Math.random() * 80) + 20,
        popularity: Math.floor(Math.random() * 40) + 60
    }))
];
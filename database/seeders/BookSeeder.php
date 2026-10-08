<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '9780743273565',
                'year' => 1925,
                'quantity' => 5
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'isbn' => '9780061120084',
                'year' => 1960,
                'quantity' => 3
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'isbn' => '9780451524935',
                'year' => 1949,
                'quantity' => 4
            ],
            [
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'isbn' => '9780141439518',
                'year' => 1813,
                'quantity' => 2
            ],
            [
                'title' => 'The Catcher in the Rye',
                'author' => 'J.D. Salinger',
                'isbn' => '9780316769174',
                'year' => 1951,
                'quantity' => 3
            ],
            [
                'title' => 'Harry Potter and the Sorcerer\'s Stone',
                'author' => 'J.K. Rowling',
                'isbn' => '9780439708180',
                'year' => 1997,
                'quantity' => 6
            ],
            [
                'title' => 'The Alchemist',
                'author' => 'Paulo Coelho',
                'isbn' => '9780062315007',
                'year' => 1988,
                'quantity' => 4
            ],
            [
                'title' => 'Brave New World',
                'author' => 'Aldous Huxley',
                'isbn' => '9780060850524',
                'year' => 1932,
                'quantity' => 3
            ]
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}
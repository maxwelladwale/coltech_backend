<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('excerpt')
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('featured_image')
                    ->image(),
                TextInput::make('author')
                    ->required()
                    ->default('COLTECH Team'),
                TextInput::make('tags'),
                Toggle::make('published')
                    ->required(),
                DateTimePicker::make('published_at'),
                TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('ai_generated')
                    ->required(),
            ]);
    }
}

<?php

namespace App\Enums;

enum SectionType: string
{
    case Hero = 'hero';
    case Slogan = 'slogan';
    case About = 'about';
    case HowItWorks = 'how_it_works';
    case FeaturedProducts = 'featured_products';
    case Categories = 'categories';
    case Gallery = 'gallery';
    case Location = 'location';
    case Contact = 'contact';
    case Footer = 'footer';

    /** Hero and footer keep their place: the owner cannot move them. */
    public function isFixed(): bool
    {
        return $this === self::Hero || $this === self::Footer;
    }

    /** @return list<string> Keys inside `content` that point to media rows. */
    public function mediaFields(): array
    {
        return match ($this) {
            self::Hero => ['background_media_id'],
            self::About => ['media_id'],
            default => [],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Portada',
            self::Slogan => 'Lema',
            self::About => 'Sobre la tienda',
            self::HowItWorks => 'Cómo funciona',
            self::FeaturedProducts => 'Productos destacados',
            self::Categories => 'Categorías',
            self::Gallery => 'Galería',
            self::Location => 'Ubicación',
            self::Contact => 'Contacto',
            self::Footer => 'Pie de página',
        };
    }
}

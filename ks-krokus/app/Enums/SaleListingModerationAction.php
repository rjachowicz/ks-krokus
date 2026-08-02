<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleListingModerationAction: string
{
    case Created = 'created';
    case Edited = 'edited';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Hidden = 'hidden';
    case Unhidden = 'unhidden';
    case Flagged = 'flagged';
    case Sold = 'sold';
    case Expired = 'expired';
    case Archived = 'archived';
    case Deleted = 'deleted';
    case Restored = 'restored';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Utworzono',
            self::Edited => 'Edytowano',
            self::Submitted => 'Wysłano do moderacji',
            self::Approved => 'Zatwierdzono',
            self::Rejected => 'Odrzucono',
            self::Hidden => 'Ukryto',
            self::Unhidden => 'Przywrócono widoczność',
            self::Flagged => 'Zgłoszono administratorowi',
            self::Sold => 'Oznaczono jako sprzedane',
            self::Expired => 'Oznaczono jako wygasłe',
            self::Archived => 'Zarchiwizowano',
            self::Deleted => 'Przeniesiono do kosza',
            self::Restored => 'Przywrócono z kosza',
        };
    }
}

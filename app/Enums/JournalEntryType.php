<?php

namespace App\Enums;

enum JournalEntryType: string
{
    case Complaint = 'complaint';
    case Symptom = 'symptom';
    case Note = 'note';
}


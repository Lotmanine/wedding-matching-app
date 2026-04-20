<?php

declare(strict_types=1);

namespace App\Enums;

enum Wilaya: string
{
    case Adrar            = '01';
    case Chlef            = '02';
    case Laghouat         = '03';
    case OumElBouaghi     = '04';
    case Batna            = '05';
    case Bejaia           = '06';
    case Biskra           = '07';
    case Bechar           = '08';
    case Blida            = '09';
    case Bouira           = '10';
    case Tamanrasset      = '11';
    case Tebessa          = '12';
    case Tlemcen          = '13';
    case Tiaret           = '14';
    case TiziOuzou        = '15';
    case Alger            = '16';
    case Djelfa           = '17';
    case Jijel            = '18';
    case Setif            = '19';
    case Saida            = '20';
    case Skikda           = '21';
    case SidiBelAbbes     = '22';
    case Annaba           = '23';
    case Guelma           = '24';
    case Constantine      = '25';
    case Medea            = '26';
    case Mostaganem       = '27';
    case MSila            = '28';
    case Mascara          = '29';
    case Ouargla          = '30';
    case Oran             = '31';
    case ElBayadh         = '32';
    case Illizi           = '33';
    case BordjBouArreridj = '34';
    case Boumerdes        = '35';
    case ElTarf           = '36';
    case Tindouf          = '37';
    case Tissemsilt       = '38';
    case ElOued           = '39';
    case Khenchela        = '40';
    case SoukAhras        = '41';
    case Tipaza           = '42';
    case Mila             = '43';
    case AinDefla         = '44';
    case Naama            = '45';
    case AinTemouchent    = '46';
    case Ghardaia         = '47';
    case Relizane         = '48';
    case ElMGhair         = '49';
    case ElMeniaa         = '50';
    case OuledDjellal     = '51';
    case BordjBadjiMokhtar= '52';
    case BeniAbbes        = '53';
    case Timimoun         = '54';
    case Touggourt        = '55';
    case Djanet           = '56';
    case InSalah          = '57';
    case InGuezzam        = '58';

    public function label(): string
    {
        return match($this) {
            self::Adrar            => 'Adrar',
            self::Chlef            => 'Chlef',
            self::Laghouat         => 'Laghouat',
            self::OumElBouaghi     => 'Oum El Bouaghi',
            self::Batna            => 'Batna',
            self::Bejaia           => 'Béjaïa',
            self::Biskra           => 'Biskra',
            self::Bechar           => 'Béchar',
            self::Blida            => 'Blida',
            self::Bouira           => 'Bouira',
            self::Tamanrasset      => 'Tamanrasset',
            self::Tebessa          => 'Tébessa',
            self::Tlemcen          => 'Tlemcen',
            self::Tiaret           => 'Tiaret',
            self::TiziOuzou        => 'Tizi Ouzou',
            self::Alger            => 'Alger',
            self::Djelfa           => 'Djelfa',
            self::Jijel            => 'Jijel',
            self::Setif            => 'Sétif',
            self::Saida            => 'Saïda',
            self::Skikda           => 'Skikda',
            self::SidiBelAbbes     => 'Sidi Bel Abbès',
            self::Annaba           => 'Annaba',
            self::Guelma           => 'Guelma',
            self::Constantine      => 'Constantine',
            self::Medea            => 'Médéa',
            self::Mostaganem       => 'Mostaganem',
            self::MSila            => 'M\'Sila',
            self::Mascara          => 'Mascara',
            self::Ouargla          => 'Ouargla',
            self::Oran             => 'Oran',
            self::ElBayadh         => 'El Bayadh',
            self::Illizi           => 'Illizi',
            self::BordjBouArreridj => 'Bordj Bou Arréridj',
            self::Boumerdes        => 'Boumerdès',
            self::ElTarf           => 'El Tarf',
            self::Tindouf          => 'Tindouf',
            self::Tissemsilt       => 'Tissemsilt',
            self::ElOued           => 'El Oued',
            self::Khenchela        => 'Khenchela',
            self::SoukAhras        => 'Souk Ahras',
            self::Tipaza           => 'Tipaza',
            self::Mila             => 'Mila',
            self::AinDefla         => 'Aïn Defla',
            self::Naama            => 'Naâma',
            self::AinTemouchent    => 'Aïn Témouchent',
            self::Ghardaia         => 'Ghardaïa',
            self::Relizane         => 'Relizane',
            self::ElMGhair         => 'El M\'Ghair',
            self::ElMeniaa         => 'El Méniaa',
            self::OuledDjellal     => 'Ouled Djellal',
            self::BordjBadjiMokhtar=> 'Bordj Badji Mokhtar',
            self::BeniAbbes        => 'Béni Abbès',
            self::Timimoun         => 'Timimoun',
            self::Touggourt        => 'Touggourt',
            self::Djanet           => 'Djanet',
            self::InSalah          => 'In Salah',
            self::InGuezzam        => 'In Guezzam',
        };
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FerianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $feriantes= [

        [   'nombre'=>'María',
            'apellido'=>'González',
            'email'=>'maria@gmail.com',
            'password'=>'12345678',
            'telefono'=> '322222222',
            'rubro'=>'artesano',
            'nombre_emprendimiento'=>'Arte María',
            'instagram'=>'@maríaArts',
            'asistencia'=>true,
            'estado'=>'aprovado',

        ],
        [
            'nombre'=>'José',
            'apellido'=>'Suarez',
            'email'=>'JoséSaurez@gmail.com',
            'password'=>'12345678',
            'telefono'=>'300000000',
            'rubro'=>'Artesano',
            'nombre_emprendimiento'=>null,
            'intagram'=>null,
            'asistencia'=>true,
            'estado'=>'aprovado',

        ],
        [
            'nombre'=>'María José',
            'apellido'=>'Estevez',
            'email'=>'MajoEstevez@mail.com',
            'password'=>'12345678',
            'telefono'=>'333333333',
            'rubro'=>'Masas',
            'nombre_emprendimiento'=>'MasasMajo',
            'instagram'=>'@MajoMasas',
            'asistencia'=>true,
            'estado'=>'aprovado',
        ],
        [
            'nombre'=>'Lolita',
            'apellido'=>'Carrió',
            'email'=>'Lolitacarrió@yahoo.com',
            'password'=>'12345678',
            'telefono'=>'344444444',
            'rubro'=>'Manualidades',
            'nombre_emprendimiento'=>'LolitaTejidos',
            'instagram'=>null,
            'asistencia'=>true,
            'estado'=>'aprovado',

        ],
        [
            'nombre'=>'Luis',
            'apellido'=>'Arguello',
            'email'=>'LuisArguello@gmail.com',
            'password'=>'123456678',
            'telefono'=>'3555555555',
            'rubro'=>'artesanía',
            'nombre_emprendimiento'=>'VasosArguello',
            'instagram'=>'@ArguVasos',
            'asistencia'=>false,
            'estado'=>'desaprovado'
        ],
        [
            'nombre'=>'Cynthia',
            'apellido'=>'Cuellar',
            'email'=>null,
            'password'=>null,
            'telefono'=>'36666666',
            'rubro'=>'Revendedora',
            'nombre_emprendimiento'=>'LaliStock',
            'instagram'=>null,
            'asistencia'=>true,
            'estado'=>'aprovado'
        ],
        [
            'nombre'=>'Susana',
            'apellido'=>'gimenez',
            'email'=>'laSU@gmail.com',
            'password'=>'12345678',
            'telefono'=>'3777777777',
            'rubro'=>'Revendedora',
            'nombre_emprendimiento'=>'Susanita_La_traviesa',
            'instagram'=>'@SusanaLaTaviesa',
            'asistencia'=>true,
            'estado'=>'aprovado'
        ],
        [
            'nombre'=>'Gabriel',
            'apellido'=>'Solis',
            'email'=>'GabrielleSolis@gmail.com',
            'password'=>'123456678',
            'telefono'=>'3888888888',
            'rubro'=>'Artesano',
            'nombre_emprendimiento'=>'GabrielArtesanías',
            'instagram'=>'@GabrielArt',
            'asistencia'=>false,
            'estado'=>'desaprovado'
        ],
        [
            'nombre'=>'Lucas',
            'apellido'=>'Amadeo',
            'email'=>'LucasAmadeo@gmail.com',
            'password'=>'12345678',
            'telefono'=>'399999999',
            'rubro'=>'Manualidades',
            'nombre_emprendimiento'=>'LucasSoldaduras',
            'instagram'=>'@LucasSold',
            'asistencia'=>true,
            'estado'=>'aprovado'
        ],
        [
            'nombre'=>'Amalia',
            'apellido'=>'Granata',
            'email'=>'Amali@gmail.com',
            'password'=>'12345678',
            'telefono'=>'400000000',
            'rubro'=>'Masas',
            'nombre_emprendimiento'=>'AmaliaMasas',
            'instagram'=>'@AmaliaMasitas',
            'asistencia'=>true,
            'estado'=>'aprovado'

        ],
        [
        'nombre'=>'Geremías',
        'apellido'=>'Quiroz',
        'email'=>'GereQuiroz@gmail.com',
        'password'=>'12345678',
        'telefono'=>'4111111111',
        'rubro'=>'artesanías',
        'nombre_emprendimiento'=>'GereArtesanías',
        'instagram'=>'@GereArtesanías',
        'asistencia'=>true,
        'estado'=>'aprovado',


        ],
        [
            'nombre'=>'luisiana',
            'apellido'=>'Lopilato',
            'email'=>'Luisia@gmail.com',
            'password'=>'12345678',
            'telefono'=>'422222222',
            'rubro'=>'Manualidades',
            'nombre_emprendimiento'=>'LusianaManualidades',
            'instagram'=>'@LusianaManualidades',
            'asistencia'=>false,
            'estado'=>'aprovado'
        ],
        [
            'nombre'=>'Ignacio',
            'apellido'=>'baungartner',
            'email'=>'Ingabau@gmail.com',
            'password'=>'12345678',
            'telefono'=>'433333333',
            'rubro'=>'Revendedor',
            'nombre_emprendimiento'=>null,
            'instagram'=>null,
            'asistencia'=>true,
            'estado'=>'aprovado'
        ],
        [
            'nombre'=>'Alicia',
            'apellido'=>'llaves',
            'email'=>'Alicia@gmail.com',
            'password'=>'12345678',
            'telefono'=>'444444444',
            'rubro'=>'Masas',
            'nombre_emprendimiento'=>'@AliciaMasitas',
            'instagram'=>'@AliciaMasitas',
            'asistencia'=>true,
            'estado'=>'aprovado'
        ],

        [
            'nombre'=>'Cristina',
            'apellido'=>'Aguilera',
            'email'=>'Xtina@aguilera.com',
            'password'=>'12345678',
            'telefono'=>'455555555',
            'rubro'=>'Artesanía',
            'nombre_emprendimiento'=>'Xmanualidades',
            'instagram'=>'@Xtinamanualidades',
            'asistencia'=>false,
            'estado'=>'desaprovado'


        ],

        [
            'nombre'=>'Violetta',
            'apellido'=>'Torres',
            'email'=>'Vilu@gmail.com',
            'password'=>'12345678',
            'telefono'=>'488888888',
            'rubro'=>'Revendedora',
            'nombre_emprendimiento'=>'ViluAlajas',
            'instagram'=>'@ViluJoyas',
            'asistencia'=>true,
            'estado'=>'aprovado'

        ],

        [
           'nombre'=>'Nicolás',
           'apellido'=>'Gonzaléz',
           'email'=>'nicogonzaléz@gmail.com',
           'password'=>'12345678',
           'telefono'=>'499999999',
           'rubro'=>'Artesano',
           'nombre_empredimiento'=>'Macetas_Nico',
           'instagram'=>'@Macetanico',
           'asistencia'=>true,
           'estado'=>'aprovado'
        ],
        [
            'nombre'=>'Ruth',
            'apellido'=>'Pazzagales',
            'email'=>null,
            'password'=>null,
            'telefono'=>'50000000',
            'rubro'=>'Maualidades',
            'nombre_emprendimiento'=>null,
            'instagram'=>null,
            'asistencia'=>true,
            'estado'=>'desaprovado',

        ],
        [
          'nombre'=>'María',
          'apellido'=>'Zamudio',
          'email'=>'maría@gmail.com',
          'password'=>'12345678',
          'telefono'=>'511111111',
          'rubro'=>'Masas',
          'nombre_emprendimiento'=>'MariaMasas',
          'instagram'=>'@MariaMasas',
          'asisitencia'=>true,
          'estado'=>'aprovado'
        ],
        [
           'nombre'=>'Carmina',
           'apellido'=>'Bouvier',
           'email'=>'carmina@gmail.com',
           'passoword'=>'12345678',
           'telefono'=>'5222222222',
           'rubro'=>'Revendedora',
           'nombre_emprendimiento'=>'JoyeríasBouvier',
           'instagram'=>'@joyerías_Bouvier',
           'asistencia'=>true,
           'estado'=>'aprovado'

        ],
        [
             'nombre'=>'Roxana',
             'apellido'=>'De_la_Vega',
             'email'=>'roxy@gmail.com',
             'password'=>'12345678',
             'telefono'=>'533333333',
             'rubro'=>'Artesana',
             'nombre_emprendimiento'=>'Roxys peluches',
             'instagram'=>'Roxy_peluchesen_casita',
             'asistencia'=>true,
             'estado'=>'aprovado'
        ],
        [
           'nombre'=>'Patrick',
           'apellido'=>'Jane',
           'email'=>null,
           'password'=>null,
           'telefono'=>'544444444',
           'rubro'=>'revendedor',
           'nombre_emprendimiento'=>null,
           'instagram'=>null,
           'asistencia'=>false,
           'estado'=>'desaprovado',


        ],
        [
             'nombre'=>'Lionel',
             'apellido'=>'Messi',
             'email'=>'lionel@gmail.com',
             'password'=>12345678,
             'telefono'=>'555555555',
             'rubro'=>'Manualidades',
             'nombre_emprendimiento'=>'LiOmessi',
             'instagram'=>'@Liomessi',
             'asistencia'=>true,
             'estado'=>'aprovado'

        ],
        [
          'nombre'=>'Nicki',
          'apellido'=>'MAGAJ',
          'email'=>'festivalfalso@chino.com',
          'password'=>'12345678',
          'telefono'=>'566666666',
          'rubro'=>'revendedora',
          'nombre_emprendimiento'=>'La_tía_Chespiritaj',
          'instagram'=>'@ilovetrumpeta',
          'asistencia'=>true,
          'estado'=>'aprovado'


        ],
        [
           'nombre'=>'florinda',
           'apellido'=>'Meza',
           'email'=>'florindameza@gmail.com',
           'password'=>'25252525',
           'telefono'=>'2525252525',
           'rubro'=>'Manualidades',
           'nombre_emprendimiento'=>null,
           'instagram'=>null,
           'asistencia'=>true,
           'estado'=>'aprovado'
        ]

        ];
    }
}

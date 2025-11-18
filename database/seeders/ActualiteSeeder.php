<?php

namespace Database\Seeders;

use App\Models\Actualite;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActualiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Actualite::create([
            'idActualite' => 1,
            'titre' => 'Albisteen gehikuntza',
            'description' => 'Raphaël Audouard, BUT informatika 3. ikasturteko ikasleak, berrien zerrenda gehitu du',
            'contenu' => 'Nunc euismod, mauris luctus adipiscing  pellentesque, augue ligula pellentesque lectus, vitae posuere purus  velit a pede. Phasellus leo mi, egestas imperdiet, blandit non,  sollicitudin pharetra, enim. Nullam faucibus tellus non enim. Sed  egestas nunc eu eros. Nunc euismod venenatis urna. Phasellus  ullamcorper. Vivamus varius est ac lorem. In id pede eleifend nibh  consectetuer faucibus. Phasellus accumsan euismod elit. Etiam vitae  elit. Integer imperdiet nibh. Morbi imperdiet orci euismod mi. \nNunc velit augue, scelerisque dignissim,  lobortis et, aliquam in, risus. In eu eros. Vestibulum ante ipsum  primis in faucibus orci luctus et ultrices posuere cubilia Curae  Curabitur vulputate elit viverra augue. Mauris fringilla, tortor sit  amet malesuada mollis, sapien mi dapibus odio, ac imperdiet ligula  enim eget nisl. Quisque vitae pede a pede aliquet suscipit.  Phasellus tellus pede, viverra vestibulum, gravida id, laoreet in,  justo. Cum sociis natoque penatibus et magnis dis parturient montes,  nascetur ridiculus mus. Integer commodo luctus lectus. Mauris justo.  Duis varius eros. Sed quam. Cras lacus eros, rutrum eget, varius  quis, convallis iaculis, velit. Mauris imperdiet, metus at tristique  venenatis, purus neque pellentesque mauris, a ultrices elit lacus  nec tortor. Class aptent taciti sociosqu ad litora torquent per  conubia nostra, per inceptos hymenaeos. Praesent malesuada. Nam  lacus lectus, auctor sit amet, malesuada vel, elementum eget, metus.  Duis neque pede, facilisis eget, egestas elementum, nonummy id,  neque.\nSuspendisse porta, dolor sed fringilla  ultrices, augue mauris gravida dolor, vel sollicitudin magna dui sit  amet nunc. Mauris mollis condimentum risus. Integer ipsum. Quisque  malesuada, erat ac dictum pulvinar, magna nisl fermentum ligula,  quis euismod mauris felis non diam. Nullam sapien turpis, rutrum  vel, condimentum ac, bibendum vulputate, nulla. Vestibulum tortor  ipsum, fermentum egestas, placerat ut, vulputate et, wisi. Aliquam  erat volutpat. Donec consequat, ligula sit amet tincidunt aliquam,  nunc lorem sagittis nunc, a ullamcorper erat ante ac felis. Donec  eleifend. Nullam quam leo, lobortis non, condimentum at, tempus  consectetuer, orci. Quisque ut lorem. Donec nisl. Lorem ipsum dolor  sit amet, consectetuer adipiscing elit. Vestibulum ante ipsum primis  in faucibus orci luctus et ultrices posuere cubilia Curae Donec  porta, libero eget feugiat posuere, felis arcu pulvinar odio, vel  dapibus enim dui nec turpis.',
            'type' => 'Privée',
            'dateP' => '2025-11-05',
            'archive' => 0,
            'lien' => null,
            'idUtilisateur' => 1,
        ]);
        Actualite::create([
            'idActualite' => 2,
            'titre' => 'Actu privée ou publique ?',
            'description' => 'Désormais, si une actualité est privée, elle ne sera plus affichée à l\'accueil.',
            'contenu' => 'Sed feugiat. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Ut pellentesque augue sed urna. Vestibulum diam eros, fringilla et,  consectetuer eu, nonummy id, sapien. Nullam at lectus. In sagittis ultrices mauris. Curabitur malesuada erat sit amet massa. Fusce blandit. Aliquam erat volutpat. Aliquam euismod. Aenean vel lectus. Nunc imperdiet justo nec dolor.\nAenean sem dolor, fermentum nec, gravida  hendrerit, mattis eget, felis. Nullam non diam vitae mi lacinia  consectetuer. Fusce non massa eget quam luctus posuere. Aenean  vulputate velit. Quisque et dolor. Donec ipsum tortor, rutrum quis,  mollis eu, mollis a, pede. Donec nulla. Duis molestie. Duis lobortis  commodo purus. Pellentesque vel quam. Ut congue congue risus. Sed  ligula. Aenean dictum pede vitae felis. Donec sit amet nibh.  Maecenas eu orci. Quisque gravida quam sed massa.',
            'type' => 'Publique',
            'dateP' => '2025-11-05',
            'archive' => 0,
            'lien' => null,
            'idUtilisateur' => 1,
        ]);
    }
}

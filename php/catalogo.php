<?php
// Catálogo estático de serviços e profissionais (não depende do banco de dados)

$profissionais = [
    'ana'     => ['nome' => 'Dra. Ana Ribeiro', 'funcao' => 'Biomédica esteta',   'foto' => 'img/profissionais/ana.jpg',     'descricao' => 'Especialista em harmonização facial, botox e preenchimentos.'],
    'carla'   => ['nome' => 'Carla Menezes',    'funcao' => 'Esteticista',        'foto' => 'img/profissionais/carla.jpg',   'descricao' => 'Limpeza de pele, drenagem linfática e sessões de laser.'],
    'juliana' => ['nome' => 'Juliana Prado',    'funcao' => 'Designer de olhar',  'foto' => 'img/profissionais/juliana.jpg', 'descricao' => 'Extensão de cílios, brow lamination e micropigmentação.'],
    'marina'  => ['nome' => 'Marina Toledo',    'funcao' => 'Terapeuta reikiana', 'foto' => 'img/profissionais/marina.jpg',  'descricao' => 'Mestre em Reiki Usui, foco em equilíbrio energético.'],
];

$servicos = [
    'Preenchimento labial' => ['preco' => 1500.00, 'duracao' => 60,  'profissionais' => ['ana']],
    'Harmonização facial'  => ['preco' => 3000.00, 'duracao' => 120, 'profissionais' => ['ana']],
    'Botox'                => ['preco' => 1150.00, 'duracao' => 45,  'profissionais' => ['ana']],
    'Drenagem'             => ['preco' => 200.00,  'duracao' => 60,  'profissionais' => ['carla']],
    'Sessões de Reiki'     => ['preco' => 150.00,  'duracao' => 50,  'profissionais' => ['marina']],
    'Brow Lamination'      => ['preco' => 200.00,  'duracao' => 60,  'profissionais' => ['juliana']],
    'Extensão de cílios'   => ['preco' => 180.00,  'duracao' => 120, 'profissionais' => ['juliana']],
    'Micropigmentação'     => ['preco' => 450.00,  'duracao' => 150, 'profissionais' => ['juliana']],
    'Limpeza de pele'      => ['preco' => 150.00,  'duracao' => 90,  'profissionais' => ['carla']],
    'Sessão de laser'      => ['preco' => 250.00,  'duracao' => 10,  'profissionais' => ['carla']],
];

// Formata minutos em texto legível (ex: 90 -> "1h30")
function formatarDuracao($minutos) {
    if ($minutos < 60) return $minutos . ' min';
    $h = intdiv($minutos, 60);
    $m = $minutos % 60;
    return $h . 'h' . ($m ? sprintf('%02d', $m) : '');
}

function formatarPreco($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
?>

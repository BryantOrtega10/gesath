<div class="interiorMenu">
    <div class="headMenu">
        <div class="openMenu"></div>
        @if (isset($dataUsu))
            <div class="perfilPersona">
                <span class="nombreUsuario">
                    Hola {{ $dataUsu->primerNombre }} {{ $dataUsu->primerApellido }}
                </span>
                @if (isset($dataUsu->foto))
                    <img src="/storage/imgEmpleados/{{ $dataUsu->foto }}" />    
                @else
                    <img src="{{ URL::asset('img/menu/personaDefecto.png') }}" />
                @endif
                
            </div>
        @else
            <div class="perfilPersona">
                <span class="nombreUsuario">
                    Hola Administrador
                </span>
                <img src="{{ URL::asset('img/menu/personaDefecto.png') }}" />
            </div>
        @endif
    </div>
    <ul class="itemsMenu">
        <li class="busqueda">
            <a>
                <i class="fas fa-search"></i>
                <span class="textoMenu">Busqueda</span>
            </a>
            <div class="contTxtBusqueda">
                <input type="text" placeholder="Buscar" id="buscarMenu" />
                <div class="respMenu"></div>
            </div>            
        </li>
        <li class="cerrar_sesion">
            <a href="/notificaciones" >
                <i class="fas fa-bell"></i>
                <span class="numNotificaciones"></span>
                <span class="textoMenu">Notificaciones</span>
            </a>
        </li>
        
        @foreach ($arrMenu as $menu)
            @if (in_array($menu->idMenu,$dataUsu->permisosUsuario) || $dataUsu->fkRol == 3)
                <li class="{{ Request::is($menu->link) ? 'active' : '' }}">
                    <a href="{{$menu->link}}" >
                        <img src="{{ URL::asset($menu->imagen) }}" />
                        <span class="textoMenu">{{$menu->nombre}}</span>
                    </a>
                    @if (sizeof($menu->subItems) > 0)
                        <ul class="subMenu">
                            @foreach ($menu->subItems as $menuLv2)
                            @if (in_array($menuLv2->idMenu,$dataUsu->permisosUsuario) || $dataUsu->fkRol == 3)
                                <li class="{{ Request::is($menuLv2->link) ? 'active' : '' }}">
                                    <a href="{{$menuLv2->link}}" >
                                        <span class="textoMenu">{{$menuLv2->nombre}}</span>
                                    </a>
                                </li>
                            @endif
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endif
        @endforeach
        <li class="cerrar_sesion">
            <a href="/logout" >
                <i class="fas fa-sign-out-alt"></i>
                <span class="textoMenu">Cerrar sesión</span>
            </a>
        </li>

    </ul>

</div>

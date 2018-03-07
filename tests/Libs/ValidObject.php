<?php

namespace ReportCollection\Tests\Libs;

class ValidObject extends InvalidObject 
{
    // Objeto com propriedades pode 
    // ser importado

    public $_1 = ["Company", "Contact", "Country"];
    public $_2 = ["Alfreds Futterkiste", "Maria Anders", "Germany"];
    public $_3 = ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"];
    public $_4 = ["Ernst Handel", "Roland Mendel", "Austria"];
    public $_5 = ["Island Trading", "Helen Bennett", "UK"];
    public $_6 = ["Laughing Bacchus Winecellars", "Yoshi Tannamuri", "Canada"];
    public $_7 = ["Magazzini Alimentari Riuniti", "Giovanni Rovelli", "Italy"];
}

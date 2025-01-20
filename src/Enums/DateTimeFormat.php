<?php

namespace Atua\FilamentFields\Enums;

enum DateTimeFormat: string
{
  case DDMMYYYY = "99/99/9999";

  case DDMMYY = "99/99/99";

  case DDMMYYYYHHMM = "99/99/9999 99:99";

  case DDMMYYYYHHMMSS = "99/99/9999 99:99:99";
}

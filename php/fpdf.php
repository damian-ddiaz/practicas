
<?php

2

/*******************************************************************************

3

* FPDF                                                                         *

4

*                                                                              *

5

* Version: 1.86                                                                *

6

* Date:    2023-06-25                                                          *

7

* Author:  Olivier PLATHEY                                                     *

8

*******************************************************************************/

9

​

10

class FPDF

11

{

12

const VERSION = '1.86';

13

protected $page;               // current page number

14

protected $n;                  // current object number

15

protected $offsets;            // array of object offsets

16

protected $buffer;             // buffer holding in-memory PDF

17

protected $pages;              // array containing pages

18

protected $state;              // current document state

19

protected $compress;           // compression flag

20

protected $iconv;              // whether iconv is available

21

protected $k;                  // scale factor (number of points in user unit)

22

protected $DefOrientation;     // default orientation

23

protected $CurOrientation;     // current orientation

24

protected $StdPageSizes;       // standard page sizes

25

protected $DefPageSize;        // default page size

26

protected $CurPageSize;        // current page size

27

protected $CurRotation;        // current page rotation

28

protected $PageInfo;           // page-related data

29

protected $wPt, $hPt;          // dimensions of current page in points

30

protected $w, $h;              // dimensions of current page in user unit

31

protected $lMargin;            // left margin

32

protected $tMargin;            // top margin

33

protected $rMargin;            // right margin

34

protected $bMargin;            // page break margin

35

protected $cMargin;            // cell margin

36

protected $x, $y;              // current position in user unit

37

protected $lasth;              // height of last printed cell

38

protected $LineWidth;          // line width in user unit

39

protected $fontpath;           // directory containing fonts

40

protected $CoreFonts;          // array of core font names

41

protected $fonts;              // array of used fonts

42

protected $FontFiles;          // array of font files

43

protected $encodings;          // array of encodings

44

protected $cmaps;              // array of ToUnicode CMaps

45

protected $FontFamily;         // current font family

46

protected $FontStyle;          // current font style


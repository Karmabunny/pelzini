#include <stdlib.h>
#include <stdio.h>

/**
* This is a test file for the C parser
**/

/**
* Main method of test c application
**/
int main (int argc, char** argv)
{
  printf("Hello, World!\n");
  exit(0);
}


/**
* Adds together two numbers
*
* @param int bar The first number
* @param int baz The second number
* @return int The result
**/
int addTwo (int bar, int baz)
{
  return bar + baz;
}


/**
* Allocates some space
**/
void* allocate(int size) { return malloc(size); }


unsigned int genRandom () { return 4; }


char** getNames() {}


/**
* Returns something which needs to be dereferenced a lot
**/
unsigned short int*** manyPointers();


/**
* Gets todays stock value
*
* @author Josh 2009-04-15
*
* @return float The stock value
**/
float todaysStockValue() {
  return 123.4;
}

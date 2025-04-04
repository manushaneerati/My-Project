import java.lang.*;
class A
{
void display()
{
System.out.println("This is from class A");
}
}
class B extends A
{
void display()
{
System.out.println("This is from class B");
}
}
 class C
{
public static void main (String args[])
{
B obj=new B();
obj.display();
}
}